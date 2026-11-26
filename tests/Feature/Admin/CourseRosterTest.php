<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentQuiz;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin gap found 2026-11-25: there was no way to put an existing student
 * into a class. Decision 2026-11-26: a roster on each Course Catalog row;
 * adding never invoices; a student's last class can't be removed.
 */
class CourseRosterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Course $maths;

    private Course $science;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $teacher = Teacher::factory()->create(['application_status' => 'active'])->fresh();

        $mathsSubject = Subject::create(['name' => 'Mathematics', 'slug' => 'cr-maths-4', 'code' => 'CRM4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $scienceSubject = Subject::create(['name' => 'Science', 'slug' => 'cr-sci-4', 'code' => 'CRS4', 'education_type' => 'primary', 'form_year' => 'Year 4']);
        $this->maths = Course::create(['title' => 'Demo Maths Class', 'teacher_id' => $teacher->id, 'subject_id' => $mathsSubject->id, 'level' => 'Year 4', 'price' => 0]);
        $this->science = Course::create(['title' => 'Demo Science Class', 'teacher_id' => $teacher->id, 'subject_id' => $scienceSubject->id, 'level' => 'Year 4', 'price' => 0]);

        $user = User::factory()->create(['role' => 'student', 'name' => 'Aina Existing', 'email' => 'aina@example.test']);
        $this->student = Student::factory()->create(['user_id' => $user->id, 'level' => 'Year 4', 'status' => 'active']);
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->science->id]);
    }

    public function test_roster_lists_enrolled_and_available_students(): void
    {
        $this->actingAs($this->admin)->getJson(route('admin.courses.students', $this->maths->id))
            ->assertOk()
            ->assertJsonPath('course.title', 'Demo Maths Class')
            ->assertJsonCount(0, 'enrolled')
            ->assertJsonPath('available.0.name', 'AINA EXISTING');

        $this->actingAs($this->admin)->getJson(route('admin.courses.students', $this->science->id))
            ->assertOk()
            ->assertJsonPath('enrolled.0.name', 'AINA EXISTING')
            ->assertJsonCount(0, 'available');
    }

    public function test_admin_adds_an_existing_student_without_creating_an_invoice(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.courses.students.store', $this->maths->id), ['student_id' => $this->student->id])
            ->assertOk()
            ->assertJsonPath('enrolled.0.name', 'AINA EXISTING')
            ->assertJsonFragment(['message' => 'Student added to the class. No invoice was created; bill the new subject from Billing if needed.']);

        $this->assertDatabaseHas('enrollments', ['student_id' => $this->student->id, 'course_id' => $this->maths->id, 'status' => 'active']);
        $this->assertSame(0, Invoice::count());
        $this->assertTrue(UserNotification::where('user_id', $this->student->user_id)->where('type', 'enrolment')->exists());
    }

    public function test_added_student_sees_the_class_quizzes_and_gets_progress_from_past_results(): void
    {
        $quiz = Quiz::factory()->create(['course_id' => $this->maths->id, 'title' => 'Fractions Drill']);
        Question::factory()->count(2)->create(['quiz_id' => $quiz->id]);
        StudentQuiz::create(['student_id' => $this->student->id, 'quiz_id' => $quiz->id, 'score' => 2, 'total_questions' => 2, 'completed_at' => now()]);

        $this->actingAs($this->student->user)->get(route('student.quiz.show', $quiz->id))->assertNotFound();

        $this->actingAs($this->admin)
            ->postJson(route('admin.courses.students.store', $this->maths->id), ['student_id' => $this->student->id])
            ->assertOk();

        $this->actingAs($this->student->user)->get(route('student.quiz.show', $quiz->id))->assertOk();
        $this->assertSame(100, (int) Enrollment::where('student_id', $this->student->id)->where('course_id', $this->maths->id)->value('progress_pct'));
    }

    public function test_adding_a_student_twice_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.courses.students.store', $this->science->id), ['student_id' => $this->student->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('student_id');

        $this->assertSame(1, Enrollment::where('student_id', $this->student->id)->count());
    }

    public function test_admin_removes_a_student_who_has_another_class(): void
    {
        Enrollment::create(['student_id' => $this->student->id, 'course_id' => $this->maths->id]);

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.courses.students.destroy', [$this->maths->id, $this->student->id]))
            ->assertOk()
            ->assertJsonCount(0, 'enrolled');

        $this->assertDatabaseMissing('enrollments', ['student_id' => $this->student->id, 'course_id' => $this->maths->id]);
        $this->assertDatabaseHas('enrollments', ['student_id' => $this->student->id, 'course_id' => $this->science->id]);
    }

    public function test_a_students_last_class_cannot_be_removed(): void
    {
        $this->actingAs($this->admin)
            ->deleteJson(route('admin.courses.students.destroy', [$this->science->id, $this->student->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('student_id');

        $this->assertDatabaseHas('enrollments', ['student_id' => $this->student->id, 'course_id' => $this->science->id]);
    }

    public function test_non_admins_cannot_use_the_roster(): void
    {
        $this->actingAs($this->student->user)
            ->postJson(route('admin.courses.students.store', $this->maths->id), ['student_id' => $this->student->id])
            ->assertForbidden();

        $this->assertDatabaseMissing('enrollments', ['course_id' => $this->maths->id]);
    }

    public function test_course_catalog_shows_the_roster_button_and_the_new_header(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('openCourseRosterModal('.$this->maths->id.')', false)
            ->assertSee('Monthly fees are set on each subject.')
            ->assertDontSee('configure tuition fees');
    }
}
