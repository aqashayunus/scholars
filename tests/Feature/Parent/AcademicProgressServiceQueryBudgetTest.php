<?php

namespace Tests\Feature\Parent;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;
use App\Models\Subject;
use App\Services\Parent\AcademicProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CountsQueries;
use Tests\TestCase;

/**
 * Query-budget regression gate (Phase 5, Step 5e) for
 * AcademicProgressService::getChildProgress()'s N+1 fix (Step 5d): before
 * the fix this fired one quiz-average query per enrollment. Proves the
 * query count stays flat as enrollment count grows.
 */
class AcademicProgressServiceQueryBudgetTest extends TestCase
{
    use RefreshDatabase;
    use CountsQueries;

    public function test_query_count_does_not_scale_with_enrollment_count(): void
    {
        $student = Student::factory()->create();

        foreach (range(1, 4) as $i) {
            $subject = Subject::factory()->create();
            $course = Course::factory()->create(['subject_id' => $subject->id]);
            Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

            $quiz = Quiz::factory()->create(['course_id' => $course->id]);
            StudentQuiz::create(['student_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 8, 'total_questions' => 10, 'completed_at' => now()]);
        }

        $student->load('enrollments.course.subject');

        [$queryCount, $progress] = $this->countQueries(
            fn () => app(AcademicProgressService::class)->getChildProgress($student)
        );

        $this->assertCount(4, $progress);
        // 1 batched quiz-results query, well under what 4 per-enrollment
        // queries plus relation access would have cost.
        $this->assertLessThanOrEqual(3, $queryCount, "Expected a flat query cost, got {$queryCount} queries for 4 enrollments.");
    }
}
