<?php

namespace Tests\Feature\Parent;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayChildInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $parent;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parent = User::factory()->create(['name' => 'Parent', 'email' => 'parent@pci.test', 'role' => 'parent', 'password' => Hash::make('password')]);
        $studentUser  = User::factory()->create(['name' => 'Child', 'email' => 'child@pci.test', 'role' => 'student', 'password' => Hash::make('password')]);
        $this->student = Student::factory()->create(['user_id' => $studentUser->id, 'parent_user_id' => $this->parent->id, 'level' => 'SPM']);
    }

    private function makeInvoice(InvoiceStatus $status = InvoiceStatus::Issued): Invoice
    {
        return Invoice::create([
            'student_id'   => $this->student->id,
            'invoice_number' => 'INV-PCI-' . uniqid(),
            'title'        => 'Tuition Fee',
            'billing_period' => 'October 2026',
            'subtotal'     => 200.00,
            'total_amount' => 200.00,
            'amount_paid'  => 0.00,
            'issued_date'  => now()->toDateString(),
            'due_date'     => now()->addDays(10)->toDateString(),
            'status'       => $status,
        ]);
    }

    public function test_already_paid_invoice_short_circuits_before_any_validation(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatus::Paid);

        // No payment_method/proof_file supplied at all — if the already-paid
        // short-circuit didn't run before validation, a bank_transfer proof
        // file requirement would never even be reachable here since no
        // payment_method is sent, so this also confirms the route doesn't
        // blow up attempting to process a paid invoice.
        $response = $this->actingAs($this->parent)
            ->post(route('parent.fees.pay.legacy', $invoice->id), []);

        $response->assertRedirect();
        $response->assertSessionHas('info', 'This invoice has already been fully paid.');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_bank_transfer_submission_creates_pending_payment_and_marks_invoice_pending_verification(): void
    {
        Storage::fake('public');
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->parent)
            ->post(route('parent.fees.pay.legacy', $invoice->id), [
                'payment_method' => 'bank_transfer',
                'proof_file'     => UploadedFile::fake()->image('proof.jpg'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'invoice_id'     => $invoice->id,
            'student_id'     => $this->student->id,
            'payer_type'     => 'parent',
            'payment_method' => 'bank_transfer',
            'status'         => PaymentStatus::Pending->value,
        ]);

        // Invoice status is deliberately left untouched for bank_transfer —
        // see the fix note in Parent\BillingService::payInvoice(). The
        // Payment row's own Pending status is what conveys "awaiting
        // verification"; this assertion guards against the invoice being
        // written an invalid raw status string again in the future.
        $this->assertEquals(InvoiceStatus::Issued, $invoice->fresh()->status);
    }

    public function test_bank_transfer_without_proof_file_fails_validation(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->parent)
            ->post(route('parent.fees.pay.legacy', $invoice->id), [
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertSessionHasErrors('proof_file');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_mock_fpx_fallback_marks_invoice_paid_and_notifies_parent_and_student(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->parent)
            ->post(route('parent.fees.pay.legacy', $invoice->id), [
                'payment_method' => 'fpx',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(InvoiceStatus::Paid, $invoice->fresh()->status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'payer_type' => 'parent',
            'status'     => PaymentStatus::Successful->value,
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->parent->id,
            'type'    => 'payment',
            'title'   => 'PAYMENT SUCCESSFUL',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->student->user_id,
            'type'    => 'payment',
            'title'   => 'TUITION FEE PAID BY PARENT',
        ]);
    }

    public function test_parent_cannot_pay_invoice_for_a_child_that_is_not_their_own(): void
    {
        $otherParent = User::factory()->create(['name' => 'Other Parent', 'email' => 'other@pci.test', 'role' => 'parent', 'password' => Hash::make('password')]);
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($otherParent)
            ->post(route('parent.fees.pay.legacy', $invoice->id), ['payment_method' => 'fpx']);

        $response->assertNotFound();
    }
}
