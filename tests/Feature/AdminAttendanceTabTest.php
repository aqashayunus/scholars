<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTabTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_attendance_route_redirects_to_dashboard_attendance_fragment(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/attendance');

        $response->assertRedirect('/admin/dashboard#attendance');
    }

    public function test_admin_dashboard_renders_attendance_tab(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('id="tab-attendance"', false);
        $response->assertSee('id="attendanceTable"', false);
        $response->assertSee('Institutional Attendance Hub');
    }

    public function test_admin_can_update_attendance_policies(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/settings/attendance', [
            'late_threshold_minutes'    => 20,
            'early_join_window_minutes' => 10,
            'excused_in_denominator'    => '1',
        ]);

        $response->assertSessionHas('success');
    }
}
