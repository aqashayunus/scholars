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
 * Found in the 2026-11-25 pre-demo click-through: a teacher's quiz never
 * showed under the student's "Practice drills" tab, and the teacher's
 * review roster said "No submission yet" next to a quiz score.
 */
class QuizVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private User $studentUser;

    private Student $student;

    private Course $course;

    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();
        $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Demo Student']);
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id, 'level' => 'Year 4', 'status' => 'active']);

        // form_year set, so display_name ("Mathematics - Year 4") differs from name.
        $subject = Subject::create(['name' => 'Mathematics', 'slug' => 'qv-mathematics-year-4', 'code' => 'QVMTH4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $this->course = Course::create(['title' => 'Demo Maths Class', 'teacher_id' => $this->teacher->id, 'subject_id' => $subject->id, 'level' => 'Year 4', 'price' => 0]);
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->course->id, 'status' => 'active']);

        $this->quiz = Quiz::factory()->create(['course_id' => $this->course->id, 'title' => 'Fractions Drill', 'total_questions' => 3]);
        Question::factory()->count(3)->create(['quiz_id' => $this->quiz->id]);
    }

    public function test_practice_drill_card_is_matched_to_the_selected_course_not_the_subject_display_name(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('student.learning'))->assertOk();

        $response->assertSee('Fractions Drill');
        $response->assertSee('x-show="activeSubjectData.course_id == '.$this->course->id.'"', false);
        $response->assertDontSee("activeSubject === 'Mathematics - Year 4'", false);
        $response->assertSee('"course_id":'.$this->course->id, false);
    }

    public function test_teacher_roster_labels_a_completed_quiz_instead_of_no_submission(): void
    {
        StudentQuiz::create(['student_id' => $this->student->id, 'quiz_id' => $this->quiz->id, 'score' => 2, 'total_questions' => 3, 'completed_at' => now()]);

        $this->actingAs($this->teacher->user)->get(route('teacher.tasks'))
            ->assertOk()
            ->assertSee('Online quiz completed (latest attempt)')
            ->assertSee('"marks":"2 \/ 3"', false);
    }

    public function test_teacher_roster_labels_an_unattempted_quiz(): void
    {
        $this->actingAs($this->teacher->user)->get(route('teacher.tasks'))
            ->assertOk()
            ->assertSee('Not attempted yet');
    }
}
