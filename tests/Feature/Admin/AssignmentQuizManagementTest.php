<?php

namespace Tests\Feature\Admin;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentQuizManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── Tasks ────────────────────────────────────────────────────────────────

    public function test_admin_can_create_a_global_task_and_it_is_assigned_to_matching_students(): void
    {
        Student::factory()->create(['level' => 'SPM']);
        Student::factory()->create(['level' => 'SPM']);
        Student::factory()->create(['level' => 'PT3']);

        $response = $this->actingAs($this->admin)->post(route('admin.tasks.store'), [
            'title'        => 'Chapter 1 Homework',
            'type'         => 'Homework',
            'target_level' => 'SPM',
        ]);

        $response->assertRedirect();
        $assignment = Assignment::where('title', 'Chapter 1 Homework')->firstOrFail();
        $this->assertTrue($assignment->is_global);
        $this->assertSame(2, StudentAssignment::where('assignment_id', $assignment->id)->count());
    }

    public function test_admin_can_create_a_task_scoped_to_a_single_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.tasks.store'), [
            'title'      => 'Personal Notes',
            'type'       => 'Notes',
            'student_id' => $student->id,
        ]);

        $response->assertRedirect();
        $assignment = Assignment::where('title', 'Personal Notes')->firstOrFail();
        $this->assertSame(1, StudentAssignment::where('assignment_id', $assignment->id)->count());
        $this->assertDatabaseHas('student_assignments', [
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
        ]);
    }

    public function test_admin_can_create_a_task_scoped_to_a_course_and_only_enrolled_students_are_linked(): void
    {
        $course = Course::factory()->create();
        $enrolled = Student::factory()->create();
        $notEnrolled = Student::factory()->create();
        Enrollment::create(['student_id' => $enrolled->id, 'course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($this->admin)->post(route('admin.tasks.store'), [
            'title'     => 'Course Task',
            'type'      => 'Homework',
            'course_id' => $course->id,
        ]);

        $assignment = Assignment::where('title', 'Course Task')->firstOrFail();
        $this->assertFalse($assignment->is_global);
        $this->assertDatabaseHas('student_assignments', ['assignment_id' => $assignment->id, 'student_id' => $enrolled->id]);
        $this->assertDatabaseMissing('student_assignments', ['assignment_id' => $assignment->id, 'student_id' => $notEnrolled->id]);
    }

    public function test_admin_can_create_a_task_with_a_question_builder_payload(): void
    {
        $this->actingAs($this->admin)->post(route('admin.tasks.store'), [
            'title'     => 'Diagnostic 1',
            'type'      => 'Diagnostic Test',
            'questions' => [
                ['number' => 1, 'type' => 'mcq', 'correct_answer' => 'a'],
                ['number' => 2, 'type' => 'fill_in_blank', 'correct_answer' => 'oxygen'],
            ],
        ]);

        $assignment = Assignment::where('title', 'Diagnostic 1')->firstOrFail();
        $this->assertSame(2, $assignment->total_questions);
        $this->assertSame(1, $assignment->section_b_questions);
        $this->assertSame(['1' => 'A'], $assignment->answer_key);
        $this->assertSame(['2' => 'oxygen'], $assignment->section_b_answer_key);
    }

    public function test_admin_can_update_a_task(): void
    {
        $assignment = Assignment::create(['title' => 'Old', 'type' => 'Homework', 'is_global' => true, 'created_by_admin' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.tasks.update', $assignment->id), [
            'title' => 'New Title',
            'type'  => 'Homework',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'title' => 'New Title']);
    }

    public function test_admin_can_delete_a_task_and_its_student_assignment_records_and_file(): void
    {
        Storage::fake('public');
        $filePath = UploadedFile::fake()->create('homework.pdf', 50)->store('assignments', 'public');
        $assignment = Assignment::create([
            'title' => 'To Delete', 'type' => 'Homework', 'is_global' => true, 'created_by_admin' => true,
            'file_path' => $filePath,
        ]);
        $student = Student::factory()->create();
        StudentAssignment::create(['assignment_id' => $assignment->id, 'student_id' => $student->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)->delete(route('admin.tasks.destroy', $assignment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('student_assignments', ['assignment_id' => $assignment->id]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_parse_pdf_questions_returns_an_error_without_a_file_or_assignment(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.parse-pdf'), []);

        $response->assertStatus(400)->assertJson(['success' => false]);
    }

    public function test_admin_can_upload_a_question_image_file(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.upload-question-image'), [
            'image_file' => UploadedFile::fake()->image('question.png'),
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $url = $response->json('url');
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));
    }

    public function test_upload_question_image_without_any_input_fails(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.tasks.upload-question-image'), []);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_admin_can_grade_a_student_submission(): void
    {
        $assignment = Assignment::create(['title' => 'Task', 'type' => 'Homework', 'is_global' => true, 'created_by_admin' => true]);
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.tasks.grade', [$assignment->id, $student->id]), [
            'score'            => 85,
            'teacher_feedback' => 'Great work',
        ]);

        $response->assertRedirect();
        $sub = StudentAssignment::where('assignment_id', $assignment->id)->where('student_id', $student->id)->firstOrFail();
        $this->assertSame(85, $sub->score);
        $this->assertSame('graded', $sub->status->value);
    }

    public function test_admin_can_view_task_submissions(): void
    {
        $assignment = Assignment::create(['title' => 'Task', 'type' => 'Homework', 'is_global' => true, 'created_by_admin' => true, 'total_marks' => 100]);
        $student = Student::factory()->create();
        StudentAssignment::create(['assignment_id' => $assignment->id, 'student_id' => $student->id, 'status' => 'submitted']);

        $response = $this->actingAs($this->admin)->getJson(route('admin.tasks.submissions', $assignment->id));

        $response->assertOk()->assertJsonPath('task.id', $assignment->id)->assertJsonCount(1, 'submissions');
    }

    // ── Quiz ─────────────────────────────────────────────────────────────────

    public function test_admin_can_create_a_quiz_and_enrolled_students_are_notified(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $response = $this->actingAs($this->admin)->post(route('admin.quizzes.store'), [
            'course_id'       => $course->id,
            'title'           => 'Practice Quiz 1',
            'total_questions' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', ['title' => 'Practice Quiz 1']);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->user_id,
            'type'    => 'quiz',
            'title'   => 'NEW DRILL QUIZ ADDED',
        ]);
    }

    public function test_admin_can_update_a_quiz(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Old', 'total_questions' => 5]);

        $response = $this->actingAs($this->admin)->put(route('admin.quizzes.update', $quiz->id), [
            'course_id'       => $course->id,
            'title'           => 'New',
            'total_questions' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'New', 'total_questions' => 10]);
    }

    public function test_admin_can_delete_a_quiz(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'To Remove', 'total_questions' => 5]);

        $response = $this->actingAs($this->admin)->delete(route('admin.quizzes.destroy', $quiz->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    // ── Question ─────────────────────────────────────────────────────────────

    public function test_admin_can_add_a_text_question_to_a_quiz(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Quiz', 'total_questions' => 5]);

        $response = $this->actingAs($this->admin)->post(route('admin.quizzes.questions.store', $quiz->id), [
            'type'           => 'text',
            'question_text'  => 'What is 2+2?',
            'option_a'       => '3',
            'option_b'       => '4',
            'option_c'       => '5',
            'option_d'       => '6',
            'correct_option' => 'B',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', ['quiz_id' => $quiz->id, 'question_text' => 'What is 2+2?']);
    }

    public function test_admin_can_update_a_question(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Quiz', 'total_questions' => 5]);
        $question = Question::create([
            'quiz_id' => $quiz->id, 'type' => 'text', 'question_text' => 'Old?',
            'option_a' => 'a', 'option_b' => 'b', 'option_c' => 'c', 'option_d' => 'd', 'correct_option' => 'A',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.questions.update', $question->id), [
            'type'           => 'text',
            'question_text'  => 'New?',
            'option_a'       => 'a',
            'option_b'       => 'b',
            'option_c'       => 'c',
            'option_d'       => 'd',
            'correct_option' => 'B',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', ['id' => $question->id, 'question_text' => 'New?', 'correct_option' => 'B']);
    }

    public function test_admin_can_delete_a_question(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Quiz', 'total_questions' => 5]);
        $question = Question::create([
            'quiz_id' => $quiz->id, 'type' => 'text', 'question_text' => 'Q?',
            'option_a' => 'a', 'option_b' => 'b', 'option_c' => 'c', 'option_d' => 'd', 'correct_option' => 'A',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.questions.destroy', $question->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }
}
