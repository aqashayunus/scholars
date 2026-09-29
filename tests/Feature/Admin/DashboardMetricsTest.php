<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The Phase-1 smoke test covers a cold, empty database. This exercises
     * the extracted revenue-stream categorization loop and the diagnostic
     * test aggregation branch with real rows, which the cold-DB smoke test
     * never touches.
     */
    public function test_admin_dashboard_categorizes_a_course_payment_and_shows_correct_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        Payment::create([
            'user_id'     => $student->user_id,
            'student_id'  => $student->id,
            'course_id'   => $course->id,
            'description' => 'Monthly course fee',
            'amount'      => 150.00,
            'status'      => PaymentStatus::Successful,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalRevenue', 150.0);
        $response->assertViewHas('revenueData', function ($data) {
            $labels = ['Course Fees', 'Enrolled Tuition', 'Assessments', 'Materials', 'Mastery Subscriptions'];
            $byLabel = array_combine($labels, $data);
            return $byLabel['Course Fees'] === 150.0;
        });
    }

    public function test_admin_dashboard_diagnostic_insights_reflect_a_graded_attempt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $diagnostic = Assignment::create([
            'course_id' => Course::factory()->create()->id,
            'title'     => 'Baseline Diagnostic',
            'type'      => 'Diagnostic Test',
        ]);
        StudentAssignment::create([
            'student_id'    => $student->id,
            'assignment_id' => $diagnostic->id,
            'status'        => 'graded',
            'score'         => 90,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['diagnostic_id' => $diagnostic->id]));

        $response->assertOk();
        $response->assertViewHas('diagnosticCompletedCount', 1);
        $response->assertViewHas('diagnosticCemerlangCount', 1);
    }
}
