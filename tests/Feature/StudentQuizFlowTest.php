<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Demo risks found 2026-11-25/26: the quiz menu only opened the first quiz,
 * an empty quiz showed a hard-coded sample question, the answer key was in
 * the page source, any student could open any quiz, and the browser sent
 * its own score.
 */
class StudentQuizFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private Course $course;

    private Quiz $quiz;

    /** @var array<int, Question> */
    private array $questions;

    protected function setUp(): void
    {
        parent::setUp();

        $teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'level' => 'Year 4', 'status' => 'active']);

        $subject = Subject::create(['name' => 'Mathematics', 'slug' => 'sqf-maths-4', 'code' => 'SQFM4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $this->course = Course::create(['title' => 'Demo Maths Class', 'teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'level' => 'Year 4', 'price' => 0]);
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->course->id, 'status' => 'active']);

        $this->quiz = Quiz::factory()->create(['course_id' => $this->course->id, 'title' => 'Fractions Drill']);
        $this->questions = [
            Question::factory()->create(['quiz_id' => $this->quiz->id, 'correct_option' => 'B', 'explanation' => 'SECRET-TIP-ONE']),
            Question::factory()->create(['quiz_id' => $this->quiz->id, 'correct_option' => 'D', 'explanation' => 'SECRET-TIP-TWO']),
        ];
    }

    private function otherCourseQuiz(): Quiz
    {
        $teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();
        $subject = Subject::create(['name' => 'Science', 'slug' => 'sqf-sci-4', 'code' => 'SQFS4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $course = Course::create(['title' => 'Other Class', 'teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'level' => 'Year 4', 'price' => 0]);
        $quiz = Quiz::factory()->create(['course_id' => $course->id, 'title' => 'Not Your Quiz']);
        Question::factory()->create(['quiz_id' => $quiz->id, 'correct_option' => 'A']);

        return $quiz;
    }

    public function test_quiz_menu_lists_every_quiz_instead_of_opening_the_first(): void
    {
        $second = Quiz::factory()->create(['course_id' => $this->course->id, 'title' => 'Decimals Drill']);
        Question::factory()->create(['quiz_id' => $second->id]);
        Quiz::factory()->create(['course_id' => $this->course->id, 'title' => 'Empty Draft Quiz']);
        $this->otherCourseQuiz();
        StudentQuiz::create(['student_id' => $this->student->id, 'quiz_id' => $this->quiz->id, 'score' => 1, 'total_questions' => 2, 'completed_at' => now()]);

        $this->actingAs($this->studentUser)->get(route('student.quiz'))
            ->assertOk()
            ->assertSee('My Quizzes')
            ->assertSee('Fractions Drill')
            ->assertSee('Decimals Drill')
            ->assertSee('Last score: 1 / 2 (50%)')
            ->assertSee('Not attempted yet')
            ->assertDontSee('Empty Draft Quiz')
            ->assertDontSee('Not Your Quiz');
    }

    public function test_quiz_menu_shows_an_empty_state_without_a_sample_question(): void
    {
        Enrollment::query()->delete();

        $this->actingAs($this->studentUser)->get(route('student.quiz'))
            ->assertOk()
            ->assertSee('No quizzes available yet.')
            ->assertDontSee('3x + 9 = 24');
    }

    public function test_an_empty_quiz_shows_a_notice_instead_of_a_fake_question(): void
    {
        $empty = Quiz::factory()->create(['course_id' => $this->course->id, 'title' => 'Empty Draft Quiz']);

        $this->actingAs($this->studentUser)->get(route('student.quiz.show', $empty->id))
            ->assertOk()
            ->assertSee('has no questions yet')
            ->assertDontSee('3x + 9 = 24');
    }

    public function test_quiz_page_does_not_contain_the_answer_key(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('student.quiz.show', $this->quiz->id))->assertOk();

        $response->assertSee('Fractions Drill');
        $response->assertSee('Demo Maths Class');
        $response->assertDontSee('SECRET-TIP-ONE');
        $response->assertDontSee('SECRET-TIP-TWO');
        $response->assertDontSee('correctAnswer: "B"', false);
        $response->assertDontSee('correctAnswer: "D"', false);
        $response->assertDontSee('Mathematics Chapter 3');
    }

    public function test_student_cannot_open_or_submit_a_quiz_from_another_class(): void
    {
        $other = $this->otherCourseQuiz();

        $this->actingAs($this->studentUser)->get(route('student.quiz.show', $other->id))->assertNotFound();
        $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $other->id), ['answers' => []])
            ->assertNotFound();

        $this->assertDatabaseMissing('student_quizzes', ['quiz_id' => $other->id]);
    }

    public function test_server_grades_the_answers_and_returns_the_review(): void
    {
        [$q1, $q2] = $this->questions;

        $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $this->quiz->id), ['answers' => [$q1->id => 'B', $q2->id => 'A']])
            ->assertOk()
            ->assertJsonPath('score', 1)
            ->assertJsonPath('total_questions', 2)
            ->assertJsonPath("review.{$q1->id}.correct_option", 'B')
            ->assertJsonPath("review.{$q2->id}.correct_option", 'D')
            ->assertJsonPath("review.{$q2->id}.explanation", 'SECRET-TIP-TWO');

        $this->assertDatabaseHas('student_quizzes', ['student_id' => $this->student->id, 'quiz_id' => $this->quiz->id, 'score' => 1, 'total_questions' => 2]);
    }

    public function test_a_client_sent_score_is_ignored(): void
    {
        [$q1] = $this->questions;

        $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $this->quiz->id), ['answers' => [$q1->id => 'A'], 'score' => 2, 'total_questions' => 2])
            ->assertOk()
            ->assertJsonPath('score', 0);

        $this->assertDatabaseHas('student_quizzes', ['quiz_id' => $this->quiz->id, 'score' => 0, 'total_questions' => 2]);
    }

    public function test_unanswered_questions_count_as_wrong_when_time_runs_out(): void
    {
        [$q1] = $this->questions;

        $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $this->quiz->id), ['answers' => [$q1->id => 'b']])
            ->assertOk()
            ->assertJsonPath('score', 1)
            ->assertJsonPath('total_questions', 2);
    }

    public function test_invalid_options_are_rejected(): void
    {
        [$q1] = $this->questions;

        $this->actingAs($this->studentUser)
            ->postJson(route('student.quiz.submit', $this->quiz->id), ['answers' => [$q1->id => 'Z']])
            ->assertUnprocessable();
    }
}
