<?php

namespace App\Services\Parent;

use App\Models\Student;
use App\Models\User;
use App\Services\StudentAccessRestrictionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChildAccessService
{
    public function __construct(private StudentAccessRestrictionService $restrictionService)
    {
    }

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
            if (!$child) {
                abort(403, 'You are not authorized to access this student\'s data.');
            }
            return $child;
        }
        return $children->first();
    }
}
