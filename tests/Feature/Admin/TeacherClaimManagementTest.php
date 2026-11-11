<?php

namespace Tests\Feature\Admin;

use App\Models\Teacher;
use App\Models\TeacherClassClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherClaimManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function makeClaim(array $overrides = []): TeacherClassClaim
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'qualification' => 'B.Ed',
            'experience_years' => 3,
        ]);

        return TeacherClassClaim::create(array_merge([
            'claim_number' => TeacherClassClaim::generateClaimNumber(),
            'teacher_id' => $teacher->id,
            'subject_name' => 'Mathematics',
            'class_title' => 'Algebra Revision',
            'class_date' => now()->subDay(),
            'duration_minutes' => 60,
            'rate_per_class' => 50.00,
            'amount' => 50.00,
            'status' => 'pending',
        ], $overrides));
    }

    public function test_admin_can_approve_a_pending_claim_and_teacher_is_notified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->makeClaim();

        $response = $this->actingAs($admin)->post(route('admin.teacher-claims.approve', $claim->id));

        $response->assertRedirect();
        $claim->refresh();
        $this->assertEquals('approved', $claim->status);
        $this->assertEquals($admin->id, $claim->approved_by);
        $this->assertNotNull($claim->approved_at);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $claim->teacher->user_id,
            'type' => 'claim',
        ]);
    }

    public function test_admin_can_reject_a_pending_claim_with_a_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->makeClaim();

        $response = $this->actingAs($admin)->post(route('admin.teacher-claims.reject', $claim->id), [
            'admin_notes' => 'Duplicate submission.',
        ]);

        $response->assertRedirect();
        $claim->refresh();
        $this->assertEquals('rejected', $claim->status);
        $this->assertEquals('Duplicate submission.', $claim->admin_notes);
    }

    public function test_admin_can_mark_an_approved_claim_as_paid_with_auto_generated_reference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->makeClaim(['status' => 'approved', 'approved_by' => null, 'approved_at' => now()]);

        $response = $this->actingAs($admin)->post(route('admin.teacher-claims.mark-paid', $claim->id), []);

        $response->assertRedirect();
        $claim->refresh();
        $this->assertEquals('paid', $claim->status);
        $this->assertEquals('Bank Transfer', $claim->payment_method);
        $this->assertNotEmpty($claim->payment_reference);
        $this->assertStringStartsWith('TRF-', $claim->payment_reference);
        $this->assertNotNull($claim->paid_at);
    }

    public function test_mark_paid_respects_explicit_payment_reference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->makeClaim(['status' => 'approved']);

        $this->actingAs($admin)->post(route('admin.teacher-claims.mark-paid', $claim->id), [
            'payment_method' => 'DuitNow QR',
            'payment_reference' => 'MBB-12345',
        ]);

        $claim->refresh();
        $this->assertEquals('DuitNow QR', $claim->payment_method);
        $this->assertEquals('MBB-12345', $claim->payment_reference);
    }

    public function test_non_admin_cannot_approve_a_claim(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $claim = $this->makeClaim();

        $response = $this->actingAs($teacher)->post(route('admin.teacher-claims.approve', $claim->id));

        $response->assertStatus(403);
    }

    public function test_teacher_claims_dashboard_data_appears_on_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->makeClaim();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('allTeacherClaims', function ($claims) use ($claim) {
            return $claims->contains('id', $claim->id);
        });
    }
}
