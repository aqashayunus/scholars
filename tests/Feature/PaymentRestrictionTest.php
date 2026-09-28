<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentAccessRestrictionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentRestrictionTest extends TestCase
{
    use RefreshDatabase;

    private User $parent;
    private Student $childPaid;
    private Student $childOverdue;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Parent User
        $this->parent = User::create([
            'name'     => 'Parent Abu',
            'email'    => 'abu@parent.test',
            'role'     => 'parent',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Child A (Paid)
        $childAUser = User::create([
            'name'     => 'Child Ahmad',
            'email'    => 'ahmad@student.test',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);

        $this->childPaid = Student::create([
            'user_id'        => $childAUser->id,
            'parent_user_id' => $this->parent->id,
            'level'          => 'SPM',
            'status'         => 'active',
        ]);

        // Link via pivot
        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_id'     => $this->childPaid->id,
            'relationship'   => 'Father',
            'is_primary'     => true,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Invoice for Child A: Paid
        Invoice::create([
            'student_id'      => $this->childPaid->id,
            'invoice_number'  => 'INV-PAID-001',
            'title'           => 'Tuition Fee - Child A',
            'billing_period'  => 'September 2026',
            'subtotal'        => 150.00,
            'total_amount'    => 150.00,
            'amount_paid'     => 150.00,
            'issued_date'     => now()->subDays(10)->toDateString(),
            'due_date'        => now()->subDays(3)->toDateString(),
            'status'          => Invoice::STATUS_PAID,
        ]);

        // 3. Create Child B (Overdue)
        $childBUser = User::create([
            'name'     => 'Child Ali',
            'email'    => 'ali@student.test',
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);

        $this->childOverdue = Student::create([
            'user_id'        => $childBUser->id,
            'parent_user_id' => $this->parent->id,
            'level'          => 'PT3',
            'status'         => 'active',
        ]);

        // Link via pivot
        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_id'     => $this->childOverdue->id,
            'relationship'   => 'Father',
            'is_primary'     => true,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Invoice for Child B: Overdue (past due date)
        Invoice::create([
            'student_id'      => $this->childOverdue->id,
            'invoice_number'  => 'INV-OVERDUE-001',
            'title'           => 'Tuition Fee - Child B',
            'billing_period'  => 'September 2026',
            'subtotal'        => 200.00,
            'total_amount'    => 200.00,
            'amount_paid'     => 0.00,
            'issued_date'     => now()->subDays(15)->toDateString(),
            'due_date'        => now()->subDays(5)->toDateString(),
            'status'          => Invoice::STATUS_OVERDUE,
        ]);
    }

    /**
     * Scenario 1: Paid Child
     * Parent accesses Child A (Fees PAID).
     * Child A academic data, progress, and classes are fully accessible.
     */
    public function test_scenario_1_paid_child_is_fully_accessible(): void
    {
        $response = $this->actingAs($this->parent)
            ->get(route('parent.dashboard', ['child_id' => $this->childPaid->id]));

        $response->assertStatus(200);
        $response->assertSee('CHILD AHMAD');
        $response->assertDontSee('Account Access Restricted');

        // Check progress route
        $progressResponse = $this->actingAs($this->parent)
            ->get(route('parent.progress', ['child_id' => $this->childPaid->id]));
        $progressResponse->assertStatus(200);

        // Check RESTful child progress route
        $restfulResponse = $this->actingAs($this->parent)
            ->get(route('parent.children.progress', $this->childPaid->id));
        $restfulResponse->assertStatus(200);
    }

    /**
     * Scenario 2: One Unpaid Child
     * Parent has Child A (PAID) and Child B (OVERDUE).
     * Parent account remains functional, Child A is accessible, Child B academic data is blocked.
     */
    public function test_scenario_2_one_unpaid_child_restricts_only_the_unpaid_child(): void
    {
        // 1. Parent can access parent dashboard
        $dashboardResponse = $this->actingAs($this->parent)
            ->get(route('parent.dashboard'));
        $dashboardResponse->assertStatus(200);

        // 2. Child A (Paid) is accessible
        $childAResponse = $this->actingAs($this->parent)
            ->get(route('parent.progress', ['child_id' => $this->childPaid->id]));
        $childAResponse->assertStatus(200);

        // 3. Child B (Overdue) is blocked from academic progress
        $childBProgress = $this->actingAs($this->parent)
            ->get(route('parent.progress', ['child_id' => $this->childOverdue->id]));
        $childBProgress->assertStatus(403);
        $childBProgress->assertSee('Account Access Restricted');
        $childBProgress->assertSee('RM 200.00');

        // 4. In parent dashboard when Child B is selected, restriction card is displayed
        $childBDashboard = $this->actingAs($this->parent)
            ->get(route('parent.dashboard', ['child_id' => $this->childOverdue->id]));
        $childBDashboard->assertStatus(200);
        $childBDashboard->assertSee('Account Access Restricted');
        $childBDashboard->assertSee('Payment Required');
        $childBDashboard->assertSee('View Outstanding Invoice');

        // 5. Parent can still access billing/fees to pay for Child B
        $feesResponse = $this->actingAs($this->parent)
            ->get(route('parent.fees'));
        $feesResponse->assertStatus(200);
    }

    /**
     * Scenario 3: All Children Unpaid
     * When both children have overdue invoices, parent can still log in and pay,
     * but academic data for both children is restricted.
     */
    public function test_scenario_3_all_children_unpaid_keeps_parent_active_but_children_restricted(): void
    {
        // Mark Child A invoice as overdue too
        Invoice::where('student_id', $this->childPaid->id)->update([
            'status'      => Invoice::STATUS_OVERDUE,
            'amount_paid' => 0.00,
        ]);

        // Parent can still log in and access dashboard
        $response = $this->actingAs($this->parent)
            ->get(route('parent.dashboard'));
        $response->assertStatus(200);

        // Academic routes for both Child A and Child B are restricted
        $this->actingAs($this->parent)
            ->get(route('parent.progress', ['child_id' => $this->childPaid->id]))
            ->assertStatus(403)
            ->assertSee('Account Access Restricted');

        $this->actingAs($this->parent)
            ->get(route('parent.progress', ['child_id' => $this->childOverdue->id]))
            ->assertStatus(403)
            ->assertSee('Account Access Restricted');

        // Parent fees page remains completely accessible
        $this->actingAs($this->parent)
            ->get(route('parent.fees'))
            ->assertStatus(200);
    }

    /**
     * Scenario 4: Payment Completed (Automatic Restoration)
     * Child B is restricted. Once invoice is paid, access is automatically restored immediately.
     */
    public function test_scenario_4_payment_completed_automatically_restores_access(): void
    {
        $service = app(StudentAccessRestrictionService::class);

        // Before payment: Child B is restricted
        $this->assertTrue($service->isStudentRestricted($this->childOverdue));

        $this->actingAs($this->parent)
            ->get(route('parent.children.progress', $this->childOverdue->id))
            ->assertStatus(403);

        // Settle the overdue invoice
        $invoice = Invoice::where('student_id', $this->childOverdue->id)->first();
        $invoice->update([
            'status'      => Invoice::STATUS_PAID,
            'amount_paid' => $invoice->total_amount,
        ]);

        // Synchronize status (simulating InvoiceService / PaymentService auto-sync)
        $service->syncStudentStatus($this->childOverdue);

        // After payment: restriction is automatically removed
        $this->assertFalse($service->isStudentRestricted($this->childOverdue));

        // Academic progress route is now immediately 200 OK without manual admin unlock
        $response = $this->actingAs($this->parent)
            ->get(route('parent.children.progress', $this->childOverdue->id));

        $response->assertStatus(200);
        $response->assertDontSee('Account Access Restricted');
    }

    /**
     * Scenario 5: Direct URL Access Protection
     * Ensures parent cannot bypass restriction by manually entering endpoints.
     */
    public function test_scenario_5_direct_url_access_is_blocked_for_restricted_child(): void
    {
        $restrictedUrls = [
            route('parent.children.progress', $this->childOverdue->id),
            route('parent.children.results', $this->childOverdue->id),
            route('parent.children.attendance', $this->childOverdue->id),
            route('parent.children.classes', $this->childOverdue->id),
            route('parent.children.assignments', $this->childOverdue->id),
            route('parent.progress', ['child_id' => $this->childOverdue->id]),
            route('parent.classes', ['child_id' => $this->childOverdue->id]),
            route('parent.attendance', ['child_id' => $this->childOverdue->id]),
        ];

        foreach ($restrictedUrls as $url) {
            $response = $this->actingAs($this->parent)->get($url);
            $response->assertStatus(403);
            $response->assertSee('Account Access Restricted');
            $response->assertSee('RM 200.00');
        }
    }

    /**
     * Student-Side Restriction Enforcement:
     * When student logs in directly, academic pages (learning, classes, tasks, quiz)
     * are blocked, while fees and profile pages remain accessible.
     */
    public function test_student_side_access_is_restricted_for_unpaid_student(): void
    {
        $studentUser = $this->childOverdue->user;

        // 1. Academic route /student/learning is blocked (403)
        $learningResponse = $this->actingAs($studentUser)->get(route('student.learning'));
        $learningResponse->assertStatus(403);
        $learningResponse->assertSee('Account Access Restricted');

        // 2. Academic route /student/classes is blocked (403)
        $classesResponse = $this->actingAs($studentUser)->get(route('student.classes'));
        $classesResponse->assertStatus(403);

        // 3. Billing route /student/fees is allowed (200)
        $feesResponse = $this->actingAs($studentUser)->get(route('student.fees'));
        $feesResponse->assertStatus(200);
    }

    /**
     * Other Parent Authorization:
     * A parent cannot access a child that does not belong to them.
     */
    public function test_parent_cannot_access_unauthorized_child(): void
    {
        $otherParent = User::create([
            'name'     => 'Other Parent',
            'email'    => 'other@parent.test',
            'role'     => 'parent',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($otherParent)
            ->get(route('parent.children.progress', $this->childPaid->id))
            ->assertStatus(403);
    }
}
