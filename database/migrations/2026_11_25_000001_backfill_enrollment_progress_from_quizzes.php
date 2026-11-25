<?php

use App\Models\Quiz;
use App\Services\CourseProgressService;
use Illuminate\Database\Migrations\Migration;

/**
 * enrollments.progress_pct was never written by any feature (always 0), so
 * the student Progress page showed no progress. It is now derived from quiz
 * results by CourseProgressService and kept in sync by model events; this
 * fills it in once for existing results.
 *
 * Only courses that have at least one quiz with questions are touched, so
 * any legacy progress values on quiz-less courses are left as they are.
 */
return new class extends Migration
{
    public function up(): void
    {
        $progress = app(CourseProgressService::class);

        Quiz::has('questions')
            ->whereNotNull('course_id')
            ->distinct()
            ->pluck('course_id')
            ->each(fn ($courseId) => $progress->recalculateCourse((int) $courseId));
    }

    public function down(): void
    {
        // Derived data; nothing to undo.
    }
};
