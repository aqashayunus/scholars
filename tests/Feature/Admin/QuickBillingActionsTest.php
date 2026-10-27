<?php

namespace Tests\Feature\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression coverage for the three admin "quick action" billing routes
 * extracted from inline Closures in routes/web.php into
 * Admin\BillingController (updateInvoiceStatusQuick,
 * recordInvoicePaymentQuick, verifyPaymentQuick). None of these had any
 * test coverage before — this also locks in the one real fix from the
 * extraction: verifyPaymentQuick now validates approved_amount, which the
 * original closure never did (a non-numeric value silently cast to 0.0).
 */
class QuickBillingActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@qba.test', 'role' => 'admin', 'password' => Hash::make('password')]);
        $studentUser = User::create(['name' => 'Student', 'email' => 'student@qba.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $this->student = Student::create(['user_id' => $studentUser->id, 'level' => 'SPM']);
    }

    private function makeInvoice(): Invoice
    {
        return Invoice::create([
            'student_id'     => $this->student->id,
            'invoice_number' => 'INV-QBA-' . uniqid(),
            'title'          => 'Tuition Fee',
            'billing_period' => 'October 2026',
            'subtotal'       => 500.00,
            'total_amount'   => 500.00,
            'amount_paid'    => 0.00,
            'issued_date'    => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'status'         => InvoiceStatus::Issued,
        ]);
    }

    public function test_admin_can_mark_invoice_paid_via_quick_status_action(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.status', $invoice->id), ['status' => 'paid']);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(InvoiceStatus::Paid, $invoice->fresh()->status);
        $this->assertEquals(500.00, $invoice->fresh()->amount_paid);
    }

    public function test_admin_can_record_a_partial_payment_via_quick_action(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.record-payment', $invoice->id), [
                'amount'         => 200.00,
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'amount' => 200.00,
            'status' => PaymentStatus::Successful->value,
        ]);
        // Payment<->Invoice is linked via the payment_invoices pivot, not a
        // direct FK column on payments.
        $this->assertDatabaseHas('payment_invoices', [
            'invoice_id'     => $invoice->id,
            'amount_applied' => 200.00,
        ]);
        $this->assertEquals(200.00, $invoice->fresh()->amount_paid);
    }

    public function test_admin_can_approve_a_manual_payment_via_quick_verify(): void
    {
        $invoice = $this->makeInvoice();
        $payment = Payment::create([
            'user_id'        => $this->student->user_id,
            'invoice_id'     => $invoice->id,
            'student_id'     => $this->student->id,
            'payer_id'       => $this->student->user_id,
            'payer_type'     => 'student',
            'description'    => 'Bank transfer proof',
            'amount'         => 500.00,
            'payment_method' => 'bank_transfer',
            'reference_no'   => 'REF-QBA-1',
            'status'         => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment->id), ['notes' => 'Verified against bank statement.']);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(PaymentStatus::Successful, $payment->fresh()->status);
    }

    public function test_quick_verify_rejects_non_numeric_approved_amount(): void
    {
        $invoice = $this->makeInvoice();
        $payment = Payment::create([
            'user_id'        => $this->student->user_id,
            'invoice_id'     => $invoice->id,
            'student_id'     => $this->student->id,
            'payer_id'       => $this->student->user_id,
            'payer_type'     => 'student',
            'description'    => 'Bank transfer proof',
            'amount'         => 500.00,
            'payment_method' => 'bank_transfer',
            'reference_no'   => 'REF-QBA-2',
            'status'         => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment->id), ['approved_amount' => 'not-a-number']);

        $response->assertSessionHasErrors('approved_amount');
        // Payment must be untouched — the pre-fix closure would have cast
        // 'not-a-number' to 0.0 and silently saved it.
        $this->assertEquals(500.00, $payment->fresh()->amount);
        $this->assertEquals(PaymentStatus::Pending, $payment->fresh()->status);
    }

    public function test_quick_verify_rejects_payment(): void
    {
        $invoice = $this->makeInvoice();
        $payment = Payment::create([
            'user_id'        => $this->student->user_id,
            'invoice_id'     => $invoice->id,
            'student_id'     => $this->student->id,
            'payer_id'       => $this->student->user_id,
            'payer_type'     => 'student',
            'description'    => 'Bank transfer proof',
            'amount'         => 500.00,
            'payment_method' => 'bank_transfer',
            'reference_no'   => 'REF-QBA-3',
            'status'         => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment->id), ['action' => 'reject', 'reason' => 'Proof illegible.']);

        $response->assertRedirect();
        $this->assertEquals(PaymentStatus::Failed, $payment->fresh()->status);
    }
}
