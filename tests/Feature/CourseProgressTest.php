<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Course progress = average over the course's quizzes (with questions) of
 * the student's latest score %, unattempted quizzes counting as 0%.
 */
class CourseProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private Course $course;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'status' => 'active']);
        $this->course = Course::factory()->create();
        $this->enrollment = Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->course->id, 'status' => 'active', 'progress_pct' => 0]);
    }

    private function quizWithQuestions(int $count, ?Course $course = null): Quiz
    {
        $quiz = Quiz::factory()->create(['course_id' => ($course ?? $this->course)->id, 'total_questions' => $count]);
        Question::factory()->count($count)->create(['quiz_id' => $quiz->id]);

        return $quiz;
    }

    private function submit(Quiz $quiz, int $score, int $total)
    {
        return $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $quiz->id), ['score' => $score, 'total_questions' => $total])
            ->assertOk();
    }

    private function progress(): int
    {
        return (int) $this->enrollment->fresh()->progress_pct;
    }

    public function test_progress_is_zero_until_a_quiz_is_attempted(): void
    {
        $this->quizWithQuestions(3);

        $this->assertSame(0, $this->progress());
    }

    public function test_progress_averages_latest_scores_and_counts_unattempted_quizzes_as_zero(): void
    {
        $q1 = $this->quizWithQuestions(3);
        $this->quizWithQuestions(4);

        $this->submit($q1, 2, 3);           // 66.7% on q1, q2 not attempted
        $this->assertSame(33, $this->progress());

        $this->submit($q1, 3, 3);           // retake replaces the score: 100%
        $this->assertSame(50, $this->progress());
    }

    public function test_a_new_quiz_lowers_progress_and_deleting_it_restores_it(): void
    {
        $q1 = $this->quizWithQuestions(2);
        $this->submit($q1, 2, 2);
        $this->assertSame(100, $this->progress());

        $q2 = $this->quizWithQuestions(2);
        $this->assertSame(50, $this->progress());

        $q2->delete();
        $this->assertSame(100, $this->progress());
    }

    public function test_quizzes_without_questions_do_not_count(): void
    {
        $q1 = $this->quizWithQuestions(2);
        Quiz::factory()->create(['course_id' => $this->course->id]); // empty quiz

        $this->submit($q1, 1, 2);

        $this->assertSame(50, $this->progress());
    }

    public function test_submitted_score_is_capped_to_the_quizs_real_question_count(): void
    {
        $q1 = $this->quizWithQuestions(3);

        $this->submit($q1, 50, 3);

        $result = StudentQuiz::where('quiz_id', $q1->id)->sole();
        $this->assertSame(3, (int) $result->score);
        $this->assertSame(3, (int) $result->total_questions);
        $this->assertSame(100, $this->progress());
    }

    public function test_other_courses_and_students_are_not_affected(): void
    {
        $otherCourse = Course::factory()->create();
        $otherEnrollment = Enrollment::create(['student_id' => $this->student->id, 'course_id' => $otherCourse->id, 'status' => 'active', 'progress_pct' => 0]);
        $this->quizWithQuestions(2, $otherCourse);

        $classmateUser = User::factory()->create(['role' => 'student']);
        $classmate = Student::factory()->create(['user_id' => $classmateUser->id, 'status' => 'active']);
        $classmateEnrollment = Enrollment::create(['student_id' => $classmate->id, 'course_id' => $this->course->id, 'status' => 'active', 'progress_pct' => 0]);

        $this->submit($this->quizWithQuestions(2), 2, 2);

        $this->assertSame(100, $this->progress());
        $this->assertSame(0, (int) $otherEnrollment->fresh()->progress_pct);
        $this->assertSame(0, (int) $classmateEnrollment->fresh()->progress_pct);
    }

    public function test_progress_page_shows_the_quiz_based_progress(): void
    {
        $this->submit($this->quizWithQuestions(4), 3, 4);

        $this->actingAs($this->studentUser)->get(route('student.progress'))
            ->assertOk()
            ->assertSee('75%');
    }

    public function test_backfill_migration_only_touches_courses_with_quizzes(): void
    {
        $quizLess = Course::factory()->create();
        $legacy = Enrollment::create(['student_id' => $this->student->id, 'course_id' => $quizLess->id, 'status' => 'active', 'progress_pct' => 40]);

        $q1 = $this->quizWithQuestions(2);
        // Insert a result without model events, as if it pre-dated this change.
        StudentQuiz::withoutEvents(fn () => StudentQuiz::create(['student_id' => $this->student->id, 'quiz_id' => $q1->id, 'score' => 1, 'total_questions' => 2, 'completed_at' => now()]));
        $this->assertSame(0, $this->progress());

        $migration = require database_path('migrations/2026_11_25_000001_backfill_enrollment_progress_from_quizzes.php');
        $migration->up();

        $this->assertSame(50, $this->progress());
        $this->assertSame(40, (int) $legacy->fresh()->progress_pct);
    }
}
