<?php

namespace App\Services;

use App\Mail\ParentLinkRequestMail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Single source of truth for linking a parent to a student.
 *
 * Previously this logic existed independently in ParentDashboardController::connectChild()
 * and ParentRegistrationController::store(), and both let a correct IC/email guess
 * immediately grant active guardianship with zero notice to anyone — including a
 * student who already had a parent on file. Consolidated here so the security fix
 * (pending + notify when a guardian already exists) can't drift between call sites
 * the way the original bug did.
 */
class ParentLinkService
{
    /**
     * @return array{status: 'active'|'pending'|'already_linked', student: Student}
     */
    public function requestLink(User $parent, Student $student, string $relationship = 'Guardian'): array
    {
        $alreadyLinkedToThisParent = DB::table('parent_student')
            ->where('parent_user_id', $parent->id)
            ->where('student_id', $student->id)
            ->exists()
            || $student->parent_user_id === $parent->id;

        if ($alreadyLinkedToThisParent) {
            return ['status' => 'already_linked', 'student' => $student];
        }

        $existingGuardianIds = $this->currentGuardianIds($student);
        $isFirstClaim = empty($existingGuardianIds);
        $status = $isFirstClaim ? 'active' : 'pending';

        $pivotId = DB::table('parent_student')->insertGetId([
            'parent_user_id' => $parent->id,
            'student_id'     => $student->id,
            'relationship'   => $relationship,
            'is_primary'     => $isFirstClaim,
            'status'         => $status,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        if ($isFirstClaim && !$student->parent_user_id) {
            // No existing guardian at all — first claim is safe to activate immediately,
            // matching the pre-fix behavior for the common legitimate case (school
            // pre-created the student account with no parent yet).
            $student->parent_user_id = $parent->id;
            $student->save();
        }

        if (!$isFirstClaim) {
            $this->notifyExistingGuardians($student, $parent, $pivotId, $existingGuardianIds);
        }

        return ['status' => $status, 'student' => $student];
    }

    /**
     * All user IDs who currently have an active guardianship claim on this student,
     * via either the legacy Student::parent_user_id column or an active pivot row.
     */
    private function currentGuardianIds(Student $student): array
    {
        $legacy = $student->parent_user_id ? [$student->parent_user_id] : [];

        $pivot = DB::table('parent_student')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('parent_user_id')
            ->toArray();

        return array_values(array_unique(array_merge($legacy, $pivot)));
    }

    private function notifyExistingGuardians(Student $student, User $requester, int $pivotId, array $guardianIds): void
    {
        $guardians = User::whereIn('id', $guardianIds)->get();

        foreach ($guardians as $guardian) {
            if (!$guardian->email) {
                continue;
            }

            Mail::to($guardian->email)->send(
                new ParentLinkRequestMail($guardian, $requester, $student, $pivotId)
            );
        }
    }
}
