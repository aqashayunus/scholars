<?php

namespace Tests\Feature\Admin;

use App\Enums\TeacherApplicationStatus;
use App\Mail\WelcomeMail;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeacherApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_approving_application_creates_teacher_account_and_emails_welcome_mail(): void
    {
        Mail::fake();

        $application = TeacherApplication::create([
            'full_name'          => 'Cikgu Aminah',
            'email'              => 'aminah@example.com',
            'phone'              => '0123456789',
            'qualification'      => 'B.Ed Mathematics',
            'preferred_subjects' => 'Mathematics',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.teacher-applications.approve', $application->id));

        $response->assertRedirect();

        $application->refresh();
        $this->assertSame(TeacherApplicationStatus::Approved, $application->status);
        $this->assertNotNull($application->user_id);

        $user = User::find($application->user_id);
        $this->assertSame('teacher', $user->role);
        $this->assertTrue((bool) $user->must_change_password);

        $this->assertDatabaseHas('teachers', [
            'user_id'             => $user->id,
            'application_status'  => 'active',
            'qualification'       => 'B.Ed Mathematics',
        ]);

        Mail::assertSent(WelcomeMail::class);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type'    => 'application',
            'title'   => 'TEACHER APPLICATION APPROVED',
        ]);
    }

    public function test_approving_application_for_existing_user_reuses_account_without_sending_welcome_mail(): void
    {
        Mail::fake();

        $existingUser = User::factory()->create(['email' => 'existing@example.com', 'role' => 'student']);
        $application = TeacherApplication::create([
            'full_name'          => 'Existing Person',
            'email'              => 'existing@example.com',
            'phone'              => '0123456780',
            'qualification'      => 'B.Ed',
            'preferred_subjects' => 'Science',
        ]);

        $this->actingAs($this->admin)->post(route('admin.teacher-applications.approve', $application->id));

        $existingUser->refresh();
        $this->assertSame('teacher', $existingUser->role);
        Mail::assertNotSent(WelcomeMail::class);
    }

    public function test_admin_can_reject_application(): void
    {
        $application = TeacherApplication::create([
            'full_name'          => 'Rejected Applicant',
            'email'              => 'rejected@example.com',
            'phone'              => '0123456781',
            'qualification'      => 'B.Ed',
            'preferred_subjects' => 'Science',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.teacher-applications.reject', $application->id), [
            'admin_notes' => 'Not enough experience.',
        ]);

        $response->assertRedirect();
        $this->assertSame(TeacherApplicationStatus::Rejected, $application->refresh()->status);
        $this->assertSame('Not enough experience.', $application->admin_notes);
    }

    public function test_admin_can_update_teacher_subjects(): void
    {
        $teacher = Teacher::factory()->create();
        $subjects = Subject::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->post(route('admin.teachers.subjects.update', $teacher->id), [
            'subject_ids' => $subjects->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertCount(2, $teacher->subjects()->get());

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $teacher->user_id,
            'type'    => 'subject',
            'title'   => 'TEACHING SUBJECTS UPDATED',
        ]);
    }

    public function test_admin_can_create_class_for_teacher_and_it_links_subject_pivot(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.teachers.classes.store', $teacher->id), [
            'title'      => 'New Class',
            'subject_id' => $subject->id,
            'level'      => 'SPM',
            'status'     => 'published',
            'price'      => 100,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', ['title' => 'New Class', 'teacher_id' => $teacher->id]);
        $this->assertTrue($teacher->subjects()->where('subject_id', $subject->id)->exists());

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $teacher->user_id,
            'type'    => 'course',
            'title'   => 'NEW CLASS ASSIGNED',
        ]);
    }
}
