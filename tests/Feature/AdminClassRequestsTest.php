<?php

namespace Tests\Feature;

use App\Enums\LiveClassStatus;
use App\Models\ClassRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminClassRequestsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacherUser;

    private Teacher $teacher;

    private Course $course;

    private LiveClass $liveClass;

    private User $studentUser;

    private User $parentUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Teacher
        $this->teacherUser = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'application_status' => 'active',
            'qualification' => 'Master of Mathematics',
            'rating' => 5.0,
        ]);

        // 3. Subject & Course
        $subject = Subject::first();
        if (! $subject) {
            $subject = Subject::create([
                'name' => 'Test Subject',
                'slug' => 'test-subject-'.uniqid(),
                'code' => 'TS-01',
                'level' => 'Form 5',
                'order' => 1,
                'is_active' => true,
                'display_name' => 'Test Subject Form 5',
            ]);
        }

        $this->course = Course::create([
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Form 5 Add Maths Intensive',
            'level' => 'Form 5',
            'status' => 'published',
        ]);

        // 4. Live Class
        $this->liveClass = LiveClass::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Calculus Derivatives Mastery',
            'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0)->setSecond(0),
            'duration_minutes' => 60,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'status' => 'upcoming',
        ]);

        // 5. Enrolled student with an active linked parent (notification recipients)
        $this->studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student = Student::create(['user_id' => $this->studentUser->id, 'level' => 'Form 5']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $this->course->id, 'status' => 'active']);

        $this->parentUser = User::factory()->create(['role' => 'parent', 'is_active' => true]);
        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parentUser->id,
            'student_id' => $student->id,
            'relationship' => 'mother',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_teacher_can_submit_cancellation_request(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->from('/teacher/classes')
            ->post(route('teacher.classes.cancel', $this->liveClass->id), [
                'reason' => 'Emergency medical appointment that cannot be rescheduled.',
            ]);

        $response->assertRedirect('/teacher/classes');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_requests', [
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'cancel',
            'status' => 'pending',
            'reason' => 'Emergency medical appointment that cannot be rescheduled.',
        ]);
    }

    public function test_teacher_can_submit_reschedule_request(): void
    {
        $newTime = now()->addDays(3)->setHour(16)->setMinute(0)->setSecond(0)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->teacherUser)
            ->from('/teacher/classes')
            ->post(route('teacher.classes.reschedule', $this->liveClass->id), [
                'reason' => 'School sports day clash with students.',
                'requested_at' => $newTime,
            ]);

        $response->assertRedirect('/teacher/classes');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_requests', [
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'reschedule',
            'status' => 'pending',
            'requested_at' => $newTime,
        ]);
    }

    public function test_admin_dashboard_renders_class_requests_tab(): void
    {
        ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'reschedule',
            'reason' => 'Conflict with faculty meeting.',
            'requested_at' => now()->addDays(4),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('tab-class-requests');
        $response->assertSee('Class Reschedule &amp; Cancellation Requests', false);
        $response->assertSee('Conflict with faculty meeting.');
    }

    public function test_admin_can_approve_reschedule_request(): void
    {
        $newTime = now()->addDays(4)->setHour(10)->setMinute(0)->setSecond(0);

        $request = ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'reschedule',
            'reason' => 'Faculty training session.',
            'requested_at' => $newTime,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.class-requests.approve', $request->id), [
                'admin_note' => 'Approved as requested. Timetable updated.',
            ]);

        $response->assertRedirect(route('admin.dashboard').'#class-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'admin_note' => 'Approved as requested. Timetable updated.',
        ]);

        $this->liveClass->refresh();
        $this->assertTrue((bool) $this->liveClass->is_rescheduled);
        $this->assertEquals($newTime->format('Y-m-d H:i:s'), $this->liveClass->scheduled_at->format('Y-m-d H:i:s'));

        // Approval goes through TimetableService::rescheduleByAdmin, which owns the notifications
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->teacherUser->id,
            'title' => 'Class Session Rescheduled by Administrator',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->studentUser->id,
            'title' => 'Class Rescheduled: '.$this->liveClass->title,
        ]);
    }

    public function test_admin_can_approve_cancellation_request(): void
    {
        $request = ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'cancel',
            'reason' => 'Sudden emergency hospital admission.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.class-requests.approve', $request->id), [
                'admin_note' => 'Approved on medical grounds.',
            ]);

        $response->assertRedirect(route('admin.dashboard').'#class-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'admin_note' => 'Approved on medical grounds.',
        ]);

        $this->liveClass->refresh();
        $this->assertSame(LiveClassStatus::Cancelled, $this->liveClass->status);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->teacherUser->id,
            'title' => 'Class Session Cancelled by Administrator',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->studentUser->id,
            'title' => 'Class Cancelled: '.$this->liveClass->title,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->parentUser->id,
            'title' => "Child's Class Cancelled ({$this->studentUser->name})",
        ]);
    }

    public function test_already_decided_request_cannot_be_approved_again(): void
    {
        $request = ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'cancel',
            'reason' => 'Double-submitted approval.',
            'status' => 'rejected',
            'admin_note' => 'Already rejected.',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.class-requests.approve', $request->id))
            ->assertSessionHasErrors('admin_note');

        $this->assertDatabaseHas('class_requests', ['id' => $request->id, 'status' => 'rejected']);
        $this->liveClass->refresh();
        $this->assertNotSame(LiveClassStatus::Cancelled, $this->liveClass->status);
    }

    public function test_admin_can_reject_class_request_with_feedback(): void
    {
        $request = ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'cancel',
            'reason' => 'Want to take a rest day.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.class-requests.reject', $request->id), [
                'admin_note' => 'Cancellation cannot be granted with less than 24h notice.',
            ]);

        $response->assertRedirect(route('admin.dashboard').'#class-requests');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'admin_note' => 'Cancellation cannot be granted with less than 24h notice.',
        ]);

        $this->liveClass->refresh();
        $this->assertNotSame(LiveClassStatus::Cancelled, $this->liveClass->status);

        // Notification to teacher with reason
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->teacherUser->id,
            'title' => 'Class Request Rejected',
        ]);
    }

    public function test_non_admin_cannot_approve_or_reject_requests(): void
    {
        $request = ClassRequest::create([
            'live_class_id' => $this->liveClass->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'cancel',
            'reason' => 'Emergency.',
            'status' => 'pending',
        ]);

        // Teacher cannot access admin approval route
        $response = $this->actingAs($this->teacherUser)
            ->post(route('admin.class-requests.approve', $request->id));
        $response->assertStatus(403);

        // Guest cannot access
        auth()->logout();
        $response2 = $this->post(route('admin.class-requests.approve', $request->id));
        $response2->assertRedirect(route('login'));
    }
}
