<?php

namespace App\Services\Parent;

use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Models\Student;
use App\Models\User;
use App\Services\Email\EmailAutomationService;
use App\Services\ParentLinkService;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChildAccessService
{
    public function __construct(private StudentAccessRestrictionService $restrictionService) {}

    /**
     * Get all students that belong to the authenticated parent.
     * Uses BOTH the legacy parent_user_id AND the new parent_student pivot
     * so existing relationships continue to work after the pivot migration.
     */
    public function getParentChildren(User $user, array $with = []): Collection
    {
        $defaultWith = ['user', 'enrollments.course.subject'];
        $with = array_unique(array_merge($defaultWith, $with));

        // IDs via legacy column (backward compat)
        $legacyIds = Student::where('parent_user_id', $user->id)->pluck('id')->toArray();

        // IDs via pivot
        $pivotIds = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->toArray();

        $allIds = array_unique(array_merge($legacyIds, $pivotIds));

        $children = Student::whereIn('id', $allIds)->with($with)->get();

        // Attach per-child restriction & payment status
        foreach ($children as $ch) {
            $ch->is_restricted = $this->restrictionService->isStudentRestricted($ch);
            $ch->payment_status = $this->restrictionService->getStudentPaymentStatus($ch);
            $ch->restriction_details = $ch->is_restricted ? $this->restrictionService->getRestrictionDetails($ch) : null;
        }

        return $children;
    }

    /**
     * Resolve a child that is AUTHORIZED to the authenticated parent.
     * Aborts with 403 if the child_id is provided but doesn't belong to this parent.
     * Falls back to the first child if no child_id is provided.
     */
    public function getAuthorizedChild(Collection $children, ?string $childId): ?Student
    {
        if ($childId) {
            $child = $children->firstWhere('id', (int) $childId);
            if (! $child) {
                abort(403, 'You are not authorized to access this student\'s data.');
            }

            return $child;
        }

        return $children->first();
    }

    /**
     * Create a brand-new child account and link it to the parent.
     * Moved verbatim out of ParentDashboardController::storeChild() — the
     * IC-duplicate check and the create-transaction stay in the same order
     * so the existing validation/redirect behaviour doesn't change.
     */
    public function createChild(User $parent, array $validated): array
    {
        $childIcHash = Student::hashIc($validated['child_ic_number']);
        if (Student::where('ic_number_hash', $childIcHash)->exists()) {
            return ['status' => 'duplicate_ic'];
        }

        try {
            $student = DB::transaction(function () use ($parent, $validated, $childIcHash) {
                // Auto-generate student email: <first name>@olympianscholars.my
                $firstName = strtolower(explode(' ', trim($validated['name']))[0] ?? 'student');
                $firstNameSlug = Str::slug($firstName, '');
                $prefix = strlen($firstNameSlug) >= 2 ? $firstNameSlug : 'student';

                $childEmail = $prefix.'@olympianscholars.my';
                $counter = 1;
                while (User::where('email', $childEmail)->exists()) {
                    $counter++;
                    $childEmail = $prefix.$counter.'@olympianscholars.my';
                }

                $childPassword = ! empty($validated['password'])
                    ? $validated['password']
                    : Str::random(8).rand(10, 99).'!';

                $childUser = new User([
                    'name' => $validated['name'],
                    'email' => $childEmail,
                    'phone' => '',
                    'password' => Hash::make($childPassword),
                ]);
                $childUser->role = 'student';
                $childUser->save();

                $dob = Student::dobFromIc($validated['child_ic_number']);
                $gender = Student::genderFromIc($validated['child_ic_number']);

                $student = new Student([
                    'user_id' => $childUser->id,
                    'ic_number_hash' => $childIcHash,
                    'ic_masked' => Student::maskedIc($validated['child_ic_number']),
                    'gender' => $gender,
                    'date_of_birth' => $dob,
                    'level' => $validated['level'],
                    'school_name' => $validated['school_name'] ?? null,
                    'total_points' => 0,
                    'streak_days' => 1,
                ]);
                $student->parent_user_id = $parent->id;
                $student->save();

                // Link via pivot table
                DB::table('parent_student')->insertOrIgnore([
                    'parent_user_id' => $parent->id,
                    'student_id' => $student->id,
                    'relationship' => $validated['relationship'] ?? 'Guardian',
                    'is_primary' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Enroll in selected subjects if provided
                $subjectNames = $validated['subjects'] ?? [];
                if (! empty($subjectNames)) {
                    StudentRegistrationController::enrollInSubjects(
                        $student, $subjectNames, $validated['level'] ?? null
                    );
                }

                return $student;
            });

            return ['status' => 'created', 'student' => $student];
        } catch (\Exception $e) {
            report($e);

            return ['status' => 'error'];
        }
    }

    /**
     * Connect an existing student to this parent by IC number or email.
     * Moved verbatim out of ParentDashboardController::connectChild().
     */
    public function connectChild(User $parent, ParentLinkService $parentLinkService, string $identifier, string $relationship): array
    {
        // Check if identifier is an IC number (12 digits)
        $cleanDigits = preg_replace('/\D/', '', $identifier);
        $student = null;

        if (strlen($cleanDigits) === 12) {
            $icHash = Student::hashIc($identifier);
            $student = Student::where('ic_number_hash', $icHash)->with('user')->first();
        }

        if (! $student) {
            // Try by student's user email
            $studentUser = User::where('email', $identifier)->where('role', 'student')->first();
            if ($studentUser) {
                $student = Student::where('user_id', $studentUser->id)->with('user')->first();
            }
        }

        if (! $student) {
            return ['status' => 'not_found'];
        }

        $result = $parentLinkService->requestLink($parent, $student, $relationship);

        if ($result['status'] === 'active') {
            // Trigger automated official communication — only fires on an actual
            // successful link, never on the pending-approval chain path (Phase 0's
            // ParentLinkService already decides whether this link activates
            // immediately or requires the existing guardian's approval first).
            try {
                EmailAutomationService::trigger(
                    'parent_student_linked',
                    [
                        'user' => $parent,
                        'parent' => $parent,
                        'student' => $student,
                    ],
                    "parent_link_{$parent->id}_{$student->id}"
                );
            } catch (\Throwable $emailErr) {
                Log::warning('[ParentDashboard] Child linked email trigger failed: '.$emailErr->getMessage());
            }
        }

        return ['status' => $result['status'], 'student' => $student];
    }
}
