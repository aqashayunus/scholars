<?php

namespace App\Services\Student;

use App\Enums\StudentAssignmentStatus;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    /**
     * Ensure all past and current course assignments + global diagnostic
     * tasks have tracking records for the student.
     */
    public function syncStudentAssignments(Student $student): void
    {
        $student->syncGlobalAndCourseAssignments();
    }

    public function tasksData(Student $student): array
    {
        $this->syncStudentAssignments($student);

        $enrollmentsMap = $student->enrollments()->pluck('enrolled_at', 'course_id')->toArray();

        $assignments = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment')
            ->with('assignment.course.subject')
            ->get()
            ->sortByDesc(function ($sa) {
                return $sa->assignment?->updated_at
                    ?? $sa->assignment?->created_at
                    ?? $sa->updated_at
                    ?? $sa->created_at;
            })
            ->values();

        // Check if each assignment was assigned before the student enrolled
        $assignments->each(function ($sa) use ($enrollmentsMap) {
            $courseId = $sa->assignment?->course_id;
            $enrolledAtStr = $enrollmentsMap[$courseId] ?? null;
            $enrolledAt = $enrolledAtStr ? Carbon::parse($enrolledAtStr) : ($sa->created_at ?? now());
            $dueDate = $sa->assignment?->due_date ? Carbon::parse($sa->assignment->due_date) : null;

            // True if assignment was due before the student enrolled in the course
            $sa->is_catch_up = $dueDate && $dueDate->lt($enrolledAt->startOfDay());
        });

        $totalTasks = $assignments->count();
        // 'completed' was never a valid StudentAssignment status. These are
        // Collection::whereIn()/where() calls (PHP-level loose comparison),
        // not query-builder calls — they need real enum cases, not strings,
        // since == between an enum instance and a string never matches.
        $completedCount = $assignments->whereIn('status', [StudentAssignmentStatus::Submitted, StudentAssignmentStatus::Graded])->count();
        $pendingCount = $assignments->where('status', StudentAssignmentStatus::Pending)->count();

        // Overdue ONLY applies to tasks due AFTER the student enrolled
        $overdueCount = $assignments->filter(function ($sa) {
            return $sa->status === StudentAssignmentStatus::Pending
                && ! $sa->is_catch_up
                && $sa->assignment
                && $sa->assignment->due_date
                && $sa->assignment->due_date->isPast();
        })->count();

        // Tasks assigned before enrollment are classified as Catch-up
        $catchUpCount = $assignments->filter(function ($sa) {
            return $sa->status === StudentAssignmentStatus::Pending && ($sa->is_catch_up ?? false);
        })->count();

        $dueThisWeekCount = $assignments->filter(function ($sa) {
            return $sa->status === StudentAssignmentStatus::Pending
                && ! $sa->is_catch_up
                && $sa->assignment
                && $sa->assignment->due_date
                && $sa->assignment->due_date->between(now()->startOfWeek(), now()->endOfWeek());
        })->count() ?: ($pendingCount - $catchUpCount);

        $completionRate = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

        $upcomingDeadlines = $assignments->filter(function ($sa) {
            return $sa->status === StudentAssignmentStatus::Pending && ! $sa->is_catch_up && $sa->assignment && $sa->assignment->due_date;
        })->sortBy(fn ($sa) => $sa->assignment->due_date)->take(5);

        $enrolledSubjects = $assignments->map(fn ($sa) => $sa->assignment?->course?->subject?->name ?? ($sa->assignment?->type === 'Diagnostic Test' ? 'Diagnostic Test' : 'General'))->filter()->unique()->values();

        $diagnosticTask = StudentAssignment::where('student_id', $student->id)
            ->whereHas('assignment', fn ($q) => $q->where('type', 'Diagnostic Test'))
            ->with('assignment')
            ->first();

        return compact(
            'assignments', 'pendingCount', 'dueThisWeekCount', 'catchUpCount',
            'completedCount', 'overdueCount', 'completionRate', 'upcomingDeadlines', 'enrolledSubjects',
            'diagnosticTask'
        );
    }

    public function submitTask(Student $student, int $id, ?string $submissionNotes, ?UploadedFile $file): void
    {
        $studentAssignment = StudentAssignment::where('student_id', $student->id)
            ->where('id', $id)
            ->firstOrFail();

        // A resubmission would reset the teacher's grade back to "submitted".
        if ($studentAssignment->status === StudentAssignmentStatus::Graded) {
            throw ValidationException::withMessages([
                'submission' => 'This task has already been graded, so it can no longer be resubmitted. Ask your teacher if you need to redo it.',
            ]);
        }

        $filePath = $studentAssignment->file_path;
        $fileName = $studentAssignment->file_name;

        if ($file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('student_submissions', 'public');
        }

        $studentAssignment->update([
            'submission_notes' => $submissionNotes,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // 1. Notify Course Teacher
        $course = $studentAssignment->assignment?->course;
        $teacherUserId = $course?->teacher?->user_id;
        $assignmentTitle = $studentAssignment->assignment?->title ?? 'Assignment';
        $studentName = $student->user?->name ?? 'Student';

        if ($teacherUserId) {
            UserNotification::create([
                'user_id' => $teacherUserId,
                'type' => 'submission',
                'title' => 'New Student Submission',
                'message' => "{$studentName} submitted the assignment '{$assignmentTitle}' for review.",
            ]);
        }

        // 2. Notify Student
        UserNotification::create([
            'user_id' => auth()->id(),
            'type' => 'task',
            'title' => 'Assignment Submitted',
            'message' => "You successfully submitted '{$assignmentTitle}'. Your teacher will review and grade your work.",
        ]);
    }
}
