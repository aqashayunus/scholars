<?php

namespace App\Policies;

use App\Models\StudentAssignmentSubmission;
use App\Models\User;

class StudentAssignmentSubmissionPolicy
{
    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, StudentAssignmentSubmission $submission): bool
    {
        // 1. Admin can view any submission
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Student can only view their own submission
        if ($user->isStudent()) {
            return $user->studentProfile && $submission->student_id === $user->studentProfile->id;
        }

        // 3. Teacher can view if they teach the course of the assignment, or if it is global
        if ($user->isTeacher()) {
            $teacher = $user->teacherProfile;
            if (!$teacher) {
                return false;
            }

            $course = $submission->assignment?->course;
            if ($course) {
                return $course->teacher_id === $teacher->id;
            }

            // Global assignments created by admin or general platform
            return true;
        }

        // 4. Parent can view if the student is their registered child
        if ($user->isParent()) {
            return $user->studentChildren()->where('students.id', $submission->student_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can save/update page annotations.
     */
    public function update(User $user, StudentAssignmentSubmission $submission): bool
    {
        // Only student owner can update their draft
        if (!$user->isStudent()) {
            return false;
        }

        if (!$user->studentProfile || $submission->student_id !== $user->studentProfile->id) {
            return false;
        }

        // Must be in draft status and not yet submitted
        return $submission->status === 'draft' && $submission->submitted_at === null;
    }

    /**
     * Determine whether the user can submit the assignment.
     */
    public function submit(User $user, StudentAssignmentSubmission $submission): bool
    {
        return $this->update($user, $submission);
    }
}
