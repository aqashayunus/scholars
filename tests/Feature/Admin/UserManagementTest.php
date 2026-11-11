<?php

namespace Tests\Feature\Admin;

use App\Enums\StudentStatus;
use App\Mail\EngagementBlastMail;
use App\Mail\WelcomeMail;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_student_user_with_random_password_and_welcome_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'role' => 'student',
            'ic_number' => '030101010101',
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'newstudent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertDatabaseHas('students', ['user_id' => $user->id]);

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        Student::create(['user_id' => $user->id, 'level' => 'SPM', 'total_points' => 0]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user->id), [
            'name' => 'Renamed User',
            'email' => $user->email,
            'role' => 'student',
        ]);

        $response->assertRedirect();
        $this->assertSame('RENAMED USER', $user->refresh()->name); // User.name casts via mb_strtoupper
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'title' => 'ACCOUNT PROFILE UPDATED',
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_reset_user_password_without_leaking_it_in_the_flash_message(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($this->admin)->post(route('admin.users.reset-password', $user->id));

        $response->assertRedirect();
        $this->assertTrue((bool) $user->refresh()->must_change_password);
        $response->assertSessionHas('success', "Password for '{$user->name}' has been reset. They will be asked to set a new password on next login.");
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'title' => 'PASSWORD UPDATED',
        ]);
    }

    /**
     * Regression: pre-fix, ucfirst() was called directly on the StudentStatus
     * enum instance (and the enum was string-interpolated into the audit log
     * detail), both of which throw against a backed enum with no __toString.
     * This route was completely broken for every invocation before the fix.
     */
    public function test_admin_can_toggle_student_status_without_crashing_on_the_enum(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::create(['user_id' => $user->id, 'level' => 'SPM', 'total_points' => 0, 'status' => StudentStatus::Active]);

        $response = $this->actingAs($this->admin)->post(route('admin.students.toggle-status', $student->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame(StudentStatus::Inactive, $student->refresh()->status);
        $this->assertFalse((bool) $user->refresh()->is_active);

        $this->assertDatabaseHas('financial_audit_logs', [
            'action' => 'student_status_changed',
            'target_id' => $student->id,
        ]);
    }

    public function test_admin_can_toggle_teacher_status(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $user->id, 'application_status' => 'active']);

        $response = $this->actingAs($this->admin)->post(route('admin.teachers.toggle-status', $teacher->id));

        $response->assertRedirect();
        $this->assertSame('inactive', $teacher->refresh()->application_status);
        $this->assertFalse((bool) $user->refresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account_via_toggle(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-is-active', $this->admin->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue((bool) $this->admin->refresh()->is_active);
    }

    public function test_admin_can_deactivate_and_reactivate_other_user(): void
    {
        $user = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.users.toggle-is-active', $user->id));
        $this->assertFalse((bool) $user->refresh()->is_active);

        $this->actingAs($this->admin)->post(route('admin.users.toggle-is-active', $user->id));
        $this->assertTrue((bool) $user->refresh()->is_active);
    }

    public function test_engagement_blast_only_reaches_non_active_users_matching_audience(): void
    {
        Mail::fake();

        $inactiveStudent = User::factory()->create(['role' => 'student', 'is_active' => false]);
        $inactiveTeacher = User::factory()->create(['role' => 'teacher', 'is_active' => false]);
        $activeStudent = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.outreach.blast-email'), [
            'blast_subject' => 'We miss you',
            'blast_body' => 'Come back!',
            'blast_audience' => 'students_only',
        ]);

        $response->assertRedirect();

        Mail::assertSent(EngagementBlastMail::class, 1);
        Mail::assertSent(EngagementBlastMail::class, function ($mail) use ($inactiveStudent) {
            return $mail->hasTo($inactiveStudent->email);
        });
    }

    public function test_export_inactive_users_csv_streams_a_csv_response(): void
    {
        User::factory()->create(['role' => 'student', 'is_active' => false, 'name' => 'Inactive Person']);

        $response = $this->actingAs($this->admin)->get(route('admin.outreach.export-inactive-csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
