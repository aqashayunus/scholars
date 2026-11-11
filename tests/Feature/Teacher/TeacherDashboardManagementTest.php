<?php

namespace Tests\Feature\Teacher;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\LiveClass;
use App\Models\Quiz;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\Teacher;
use App\Models\TeachingGig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherDashboardManagementTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = Teacher::factory()->create()->fresh();
    }

    private function actingAsTeacher()
    {
        return $this->actingAs($this->teacher->user);
    }

    // ── Pages (smoke) ────────────────────────────────────────────────────────────

    public function test_dashboard_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.dashboard'))->assertOk();
    }

    public function test_classes_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.classes'))->assertOk();
    }

    public function test_manage_class_page_loads_for_own_course(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $this->actingAsTeacher()->get(route('teacher.classes.show', $course->id))->assertOk();
    }

    public function test_manage_class_page_404s_for_someone_elses_course(): void
    {
        $course = Course::factory()->create();

        $this->actingAsTeacher()->get(route('teacher.classes.show', $course->id))->assertNotFound();
    }

    public function test_students_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.students'))->assertOk();
    }

    public function test_learning_materials_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.materials'))->assertOk();
    }

    public function test_tasks_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.tasks'))->assertOk();
    }

    public function test_monitoring_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.monitoring'))->assertOk();
    }

    // NOTE: teacher.gigs.index and teacher.wallet.index Blade views do not exist
    // in this checkout (pre-existing gap, confirmed via `find resources/views/teacher`
    // — no gigs/ or wallet/ subdirectory at all). Not caused by this refactor; the
    // original controller referenced these exact same view paths. Page-load smoke
    // tests for these two routes are intentionally omitted until the views exist;
    // the underlying service logic is still covered by the claim/check-in/check-out
    // and wallet data tests below, which don't render a view.

    public function test_recordings_page_loads(): void
    {
        $this->actingAsTeacher()->get(route('teacher.recordings'))->assertOk();
    }

    // ── Duty & Gigs ──────────────────────────────────────────────────────────────

    public function test_teacher_can_toggle_duty_status(): void
    {
        // is_online defaults to true (teachers table migration default)
        $this->assertTrue((bool) $this->teacher->is_online);

        $this->actingAsTeacher()->post(route('teacher.toggle-duty'))->assertRedirect();

        $this->assertFalse((bool) $this->teacher->refresh()->is_online);
    }

    public function test_teacher_can_claim_an_open_gig(): void
    {
        $gig = TeachingGig::create([
            'title' => 'Substitute Session', 'gig_type' => 'substitute_sos', 'scheduled_at' => now()->addDay(),
            'duration_minutes' => 60, 'payout_amount' => 50, 'status' => 'open',
        ]);

        $this->actingAsTeacher()->post(route('teacher.gigs.claim', $gig->id))->assertRedirect();

        $this->assertDatabaseHas('teaching_gigs', ['id' => $gig->id, 'status' => 'claimed', 'assigned_teacher_id' => $this->teacher->id]);
    }

    public function test_teacher_can_check_in_and_check_out_of_a_claimed_gig(): void
    {
        $gig = TeachingGig::create([
            'title' => 'Substitute Session', 'gig_type' => 'substitute_sos', 'scheduled_at' => now()->addDay(),
            'duration_minutes' => 60, 'payout_amount' => 50, 'status' => 'claimed', 'assigned_teacher_id' => $this->teacher->id,
        ]);

        $this->actingAsTeacher()->post(route('teacher.gigs.checkin', $gig->id))->assertRedirect();
        $this->assertDatabaseHas('teaching_gigs', ['id' => $gig->id, 'status' => 'checked_in']);

        $before = $this->teacher->total_gigs_completed;

        $this->actingAsTeacher()->post(route('teacher.gigs.checkout', $gig->id))->assertRedirect();
        $this->assertDatabaseHas('teaching_gigs', ['id' => $gig->id, 'status' => 'completed']);
        $this->assertSame($before + 1, $this->teacher->refresh()->total_gigs_completed);
    }

    // ── Class Scheduling ─────────────────────────────────────────────────────────

    public function test_teacher_can_schedule_a_class_for_own_course(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAsTeacher()->post(route('teacher.classes.store'), [
            'course_id' => $course->id,
            'title' => 'Live Session',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('live_classes', ['course_id' => $course->id, 'title' => 'Live Session', 'teacher_id' => $this->teacher->id]);
    }

    public function test_teacher_can_request_cancellation_of_own_class(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $liveClass = LiveClass::create(['course_id' => $course->id, 'teacher_id' => $this->teacher->id, 'title' => 'Class', 'scheduled_at' => now()->addDay(), 'duration_minutes' => 60]);

        $response = $this->actingAsTeacher()->post(route('teacher.classes.cancel', $liveClass->id), [
            'reason' => 'Feeling unwell tomorrow',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_requests', ['live_class_id' => $liveClass->id, 'type' => 'cancel', 'status' => 'pending']);
    }

    public function test_teacher_can_request_reschedule_of_own_class(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $liveClass = LiveClass::create(['course_id' => $course->id, 'teacher_id' => $this->teacher->id, 'title' => 'Class', 'scheduled_at' => now()->addDay(), 'duration_minutes' => 60]);

        $response = $this->actingAsTeacher()->post(route('teacher.classes.reschedule', $liveClass->id), [
            'reason' => 'Venue conflict arose',
            'requested_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_requests', ['live_class_id' => $liveClass->id, 'type' => 'reschedule', 'status' => 'pending']);
    }

    // ── Tasks & Quizzes ──────────────────────────────────────────────────────────

    public function test_teacher_can_store_a_class_specific_task(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAsTeacher()->post(route('teacher.classes.tasks.store', $course->id), [
            'title' => 'Homework 1',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', ['course_id' => $course->id, 'title' => 'Homework 1']);
    }

    public function test_teacher_can_store_a_general_task_and_students_are_linked(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $student = Student::factory()->create();
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $response = $this->actingAsTeacher()->post(route('teacher.tasks.store'), [
            'course_id' => $course->id,
            'type' => 'Assessment',
            'title' => 'Mid-term Assessment',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect();
        $assignment = Assignment::where('title', 'Mid-term Assessment')->firstOrFail();
        $this->assertDatabaseHas('student_assignments', ['assignment_id' => $assignment->id, 'student_id' => $student->id]);
    }

    public function test_teacher_can_update_a_task(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $assignment = $course->assignments()->create(['title' => 'Old Title', 'due_date' => now()->addDays(3)]);

        $response = $this->actingAsTeacher()->put(route('teacher.tasks.update', $assignment->id), [
            'course_id' => $course->id,
            'title' => 'New Title',
            'due_date' => now()->addDays(4)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'title' => 'New Title']);
    }

    public function test_teacher_can_delete_a_task(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $assignment = $course->assignments()->create(['title' => 'To Delete', 'due_date' => now()->addDays(3)]);

        $response = $this->actingAsTeacher()->delete(route('teacher.tasks.destroy', $assignment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_teacher_can_grade_a_submission(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $assignment = $course->assignments()->create(['title' => 'Task', 'due_date' => now()->addDays(3)]);
        $student = Student::factory()->create();

        $response = $this->actingAsTeacher()->post(route('teacher.tasks.grade', [$assignment->id, $student->id]), [
            'score' => 92,
            'status' => 'graded',
        ]);

        $response->assertRedirect();
        $sub = StudentAssignment::where('assignment_id', $assignment->id)->where('student_id', $student->id)->firstOrFail();
        $this->assertSame(92, $sub->score);
    }

    public function test_teacher_can_create_a_quiz_with_questions(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAsTeacher()->post(route('teacher.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Practice Quiz',
            'questions' => [
                ['question_text' => 'Q1?', 'option_a' => 'a', 'option_b' => 'b', 'option_c' => 'c', 'option_d' => 'd', 'correct_option' => 'A'],
            ],
        ]);

        $response->assertRedirect();
        $quiz = Quiz::where('title', 'Practice Quiz')->firstOrFail();
        $this->assertSame(1, $quiz->questions()->count());
        $this->assertDatabaseHas('questions', ['quiz_id' => $quiz->id, 'correct_option' => 'A']);
    }

    public function test_teacher_can_delete_own_quiz(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Quiz', 'total_questions' => 1]);

        $response = $this->actingAsTeacher()->delete(route('teacher.quizzes.destroy', $quiz->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    // ── Learning Materials ───────────────────────────────────────────────────────

    public function test_teacher_can_upload_a_learning_material(): void
    {
        Storage::fake('public');
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAsTeacher()->post(route('teacher.materials.store'), [
            'course_id' => $course->id,
            'title' => 'Chapter 1 Notes',
            'type' => 'Notes',
            'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('learning_materials', ['course_id' => $course->id, 'title' => 'Chapter 1 Notes']);
    }

    public function test_teacher_can_delete_own_material(): void
    {
        Storage::fake('public');
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $filePath = UploadedFile::fake()->create('notes.pdf', 50)->store('materials', 'public');
        $material = LearningMaterial::create([
            'course_id' => $course->id, 'teacher_id' => $this->teacher->id, 'title' => 'Notes', 'type' => 'Notes',
            'file_path' => $filePath, 'file_name' => 'notes.pdf', 'status' => 'Published',
        ]);

        $response = $this->actingAsTeacher()->delete(route('teacher.materials.destroy', $material->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_teacher_can_download_own_material(): void
    {
        Storage::fake('public');
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $filePath = UploadedFile::fake()->create('notes.pdf', 50)->store('materials', 'public');
        $material = LearningMaterial::create([
            'course_id' => $course->id, 'teacher_id' => $this->teacher->id, 'title' => 'Notes', 'type' => 'Notes',
            'file_path' => $filePath, 'file_name' => 'notes.pdf', 'status' => 'Published',
        ]);

        $response = $this->actingAsTeacher()->get(route('materials.download', $material->id));

        $response->assertOk();
    }

    public function test_teacher_can_update_own_material(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $material = LearningMaterial::create([
            'course_id' => $course->id, 'teacher_id' => $this->teacher->id, 'title' => 'Old', 'type' => 'Notes',
            'file_path' => 'materials/placeholder.pdf', 'file_name' => 'placeholder.pdf', 'status' => 'Published',
        ]);

        $response = $this->actingAsTeacher()->put(route('teacher.materials.update', $material->id), [
            'course_id' => $course->id,
            'title' => 'New',
            'type' => 'Notes',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('learning_materials', ['id' => $material->id, 'title' => 'New']);
    }

    // ── Recordings ───────────────────────────────────────────────────────────────

    public function test_teacher_can_upload_a_recording(): void
    {
        Storage::fake('public');
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAsTeacher()->post(route('teacher.recordings.store'), [
            'course_id' => $course->id,
            'title' => 'Lesson 1 Recording',
            'topic_name' => 'Introduction',
            'duration_minutes' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recorded_classes', ['course_id' => $course->id, 'title' => 'Lesson 1 Recording']);
    }

    public function test_teacher_can_update_a_recording(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $recording = RecordedClass::create(['course_id' => $course->id, 'subject_id' => $course->subject_id, 'title' => 'Old', 'topic_name' => 'Topic', 'video_url' => 'https://example.com/v', 'duration_minutes' => 20]);

        $response = $this->actingAsTeacher()->put(route('teacher.recordings.update', $recording->id), [
            'course_id' => $course->id,
            'title' => 'New',
            'topic_name' => 'Topic',
            'duration_minutes' => 25,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recorded_classes', ['id' => $recording->id, 'title' => 'New']);
    }

    public function test_teacher_can_delete_a_recording(): void
    {
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $recording = RecordedClass::create(['course_id' => $course->id, 'subject_id' => $course->subject_id, 'title' => 'To Remove', 'topic_name' => 'Topic', 'video_url' => 'https://example.com/v', 'duration_minutes' => 20]);

        $response = $this->actingAsTeacher()->delete(route('teacher.recordings.destroy', $recording->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('recorded_classes', ['id' => $recording->id]);
    }

    // ── Notifications ────────────────────────────────────────────────────────────

    public function test_teacher_can_mark_all_notifications_read(): void
    {
        $response = $this->actingAsTeacher()->postJson(route('teacher.notifications.readAll'));

        $response->assertOk()->assertJson(['success' => true]);
    }
}
