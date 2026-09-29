<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use App\Models\TeachingGig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GigManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_gig_with_default_status_and_zero_surge_bonus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.gigs.store'), [
            'title'            => 'Weekend SPM Physics Tuition',
            'subject_id'       => $subject->id,
            'scheduled_at'     => now()->addDays(3)->toDateTimeString(),
            'duration_minutes' => 90,
            'payout_amount'    => 120.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teaching_gigs', [
            'title'         => 'Weekend SPM Physics Tuition',
            'subject_id'    => $subject->id,
            'status'        => 'open',
            'surge_bonus'   => 0,
            'payout_amount' => 120.00,
        ]);
    }

    public function test_gig_requires_a_valid_subject(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.gigs.store'), [
            'title'            => 'Orphan Gig',
            'subject_id'       => 99999,
            'scheduled_at'     => now()->addDay()->toDateTimeString(),
            'duration_minutes' => 60,
            'payout_amount'    => 50,
        ]);

        $response->assertSessionHasErrors('subject_id');
        $this->assertSame(0, TeachingGig::count());
    }

    public function test_admin_can_remove_a_gig(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subject = Subject::factory()->create();
        $gig = TeachingGig::create([
            'subject_id'       => $subject->id,
            'title'            => 'Cancelled Session',
            'scheduled_at'     => now()->addDay(),
            'duration_minutes' => 60,
            'payout_amount'    => 80,
            'surge_bonus'      => 0,
            'status'           => 'open',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.gigs.destroy', $gig->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('teaching_gigs', ['id' => $gig->id]);
    }
}
