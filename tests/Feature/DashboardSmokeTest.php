<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 1 safety net — smoke coverage for the four dashboard controllers,
 * previously at zero test coverage entirely. Verifies the CORRECT role can
 * actually load their own dashboard without crashing, complementing
 * RouteAuthorizationMatrixTest (which only proves WRONG roles are blocked).
 *
 * Deliberately tests the coldest-start state (a freshly created user with
 * no enrollments/classes/invoices yet) since that's where auto-provisioning
 * side effects (e.g. first-login auto-enrollment) are most likely to break.
 */
class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_loads_for_a_brand_new_student_with_no_teachers_in_the_system(): void
    {
        // Deliberately zero teachers in the DB — this is the exact condition
        // that previously crashed first-time dashboard load via the
        // hardcoded teacher_id fallback in PageController::enrollStudentInSubjects.
        $user = User::create([
            'name'     => 'Fresh Student',
            'email'    => 'freshstudent@test.com',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);
        Student::create(['user_id' => $user->id, 'level' => 'SPM']);

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertOk();
    }

    public function test_teacher_dashboard_loads_for_a_brand_new_teacher(): void
    {
        $user = User::create([
            'name'     => 'Fresh Teacher',
            'email'    => 'freshteacher@test.com',
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);
        Teacher::create([
            'user_id'          => $user->id,
            'specialization'   => 'General Academic',
            'qualification'    => 'B.Ed',
            'rating'           => 5.0,
            'experience_years' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('teacher.dashboard'));

        $response->assertOk();
    }

    public function test_parent_dashboard_loads_for_a_parent_with_no_children_yet(): void
    {
        $user = User::create([
            'name'     => 'Fresh Parent',
            'email'    => 'freshparent@test.com',
            'role'     => 'parent',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get(route('parent.dashboard'));

        $response->assertOk();
    }

    public function test_admin_dashboard_loads_for_a_fresh_admin(): void
    {
        $user = User::create([
            'name'     => 'Fresh Admin',
            'email'    => 'freshadmin@test.com',
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }
}
