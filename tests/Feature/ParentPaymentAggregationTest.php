<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression coverage for a silent bug found while sweeping Phase 3's
 * StudentAssignmentStatus/TeacherApplicationStatus conversion: several
 * Payment aggregates in ParentDashboardController (dashboard() and
 * payments()) used Illuminate\Support\Collection::where()/whereIn()/
 * in_array() with raw string statuses AFTER Payment.status became a
 * backed-enum cast. Collection-level comparisons are PHP loose (==), which
 * never matches an enum instance against a raw string — so these silently
 * returned zero/null instead of throwing, and were invisible to a render
 * smoke test (the page still returned 200, just with wrong numbers).
 */
class ParentPaymentAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_payments_pages_surface_pending_and_successful_payments_correctly(): void
    {
        $parent = User::factory()->create(['name' => 'Parent', 'email' => 'parent@agg.test', 'role' => 'parent', 'password' => Hash::make('password')]);
        $studentUser = User::factory()->create(['name' => 'Student', 'email' => 'student@agg.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'parent_user_id' => $parent->id, 'level' => 'SPM']);

        Payment::create([
            'user_id' => $parent->id,
            'student_id' => $student->id,
            'payer_id' => $parent->id,
            'payer_type' => 'parent',
            'description' => 'Pending fee',
            'amount' => 150,
            'payment_method' => 'bank_transfer',
            'reference_no' => 'REF-PEND-1',
            'status' => PaymentStatus::Pending,
        ]);

        Payment::create([
            'user_id' => $parent->id,
            'student_id' => $student->id,
            'payer_id' => $parent->id,
            'payer_type' => 'parent',
            'description' => 'Paid fee',
            'amount' => 300,
            'payment_method' => 'bank_transfer',
            'reference_no' => 'REF-PAID-1',
            'status' => PaymentStatus::Successful,
            'paid_at' => now(),
        ]);

        $dashboard = $this->actingAs($parent)->get(route('parent.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertViewHas('outstanding', 150.0);
        $dashboard->assertViewHas('pendingCount', 1);
        $dashboard->assertViewHas('latestPayment', fn ($p) => $p && $p->status === PaymentStatus::Successful);

        $payments = $this->actingAs($parent)->get(route('parent.payments'));
        $payments->assertOk();
        $payments->assertViewHas('totalPaid', 300.0);
        $payments->assertViewHas('totalOutstanding', 150.0);
        $payments->assertViewHas('pendingCount', 1);
        $payments->assertViewHas('paidCount', 1);
        $payments->assertViewHas('latestPayment', fn ($p) => $p && $p->status === PaymentStatus::Successful);
        $payments->assertViewHas('pendingPayments', fn ($c) => $c->count() === 1);
    }
}
