<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\Billplz\BillplzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BillplzPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $studentUser;

    protected Student $student;

    protected BillplzService $billplzService;

    protected string $apiKey = 'test-api-key-12345';

    protected string $xSignatureKey = 'test-x-signature-key-67890';

    protected string $collectionId = 'in50b8km';

    protected function setUp(): void
    {
        parent::setUp();

        $this->billplzService = new BillplzService;

        Setting::set('billplz_api_key', $this->apiKey, 'finance');
        Setting::set('billplz_x_signature_key', $this->xSignatureKey, 'finance');
        Setting::set('billplz_collection_id', $this->collectionId, 'finance');
        Setting::set('billplz_sandbox', '1', 'finance');

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
            'email' => 'student@olympianscholars.com',
            'name' => 'Ahmad Student',
        ]);

        $this->student = Student::firstOrCreate(
            ['user_id' => $this->studentUser->id],
            [
                'parent_id' => null,
                'level' => 'Form 4',
                'birth_date' => '2008-01-01',
                'school' => 'SMK Olympian',
                'gender' => 'Male',
            ]
        );
    }

    private function createTestInvoice(float $total = 150.00, InvoiceStatus $status = InvoiceStatus::Issued): Invoice
    {
        return Invoice::factory()->create([
            'student_id' => $this->student->id,
            'invoice_number' => 'INV-'.strtoupper(uniqid()),
            'title' => 'Monthly Tuition Fee - Test',
            'description' => 'Standard secondary tuition package',
            'billing_period' => now()->format('F Y'),
            'subtotal' => $total,
            'discount_amount' => 0.00,
            'total_amount' => $total,
            'amount_paid' => 0.00,
            'issued_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => $status,
        ]);
    }

    /**
     * Test cents calculation and API payload when creating a Billplz bill.
     */
    public function test_billplz_bill_creation_and_cents_calculation(): void
    {
        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/bills' => Http::response([
                'id' => 'bill_abc123',
                'collection_id' => $this->collectionId,
                'paid' => false,
                'state' => 'due',
                'amount' => 15050,
                'url' => 'https://www.billplz-sandbox.com/bills/bill_abc123',
            ], 200),
        ]);

        $bill = $this->billplzService->createBill([
            'collection_id' => $this->collectionId,
            'email' => 'parent@example.com',
            'name' => 'Parent Ali',
            'amount' => 150.50, // RM 150.50 should convert to 15050 cents
            'callback_url' => 'https://example.com/webhook',
            'description' => 'Test Tuition Payment',
        ]);

        $this->assertEquals('bill_abc123', $bill['id']);
        $this->assertEquals('https://www.billplz-sandbox.com/bills/bill_abc123', $bill['url']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.billplz-sandbox.com/api/v3/bills'
                && $request['amount'] === 15050
                && $request['collection_id'] === $this->collectionId
                && $request['email'] === 'parent@example.com';
        });
    }

    /**
     * Test single invoice checkout redirects to Billplz hosted URL.
     */
    public function test_invoice_checkout_redirects_to_billplz_hosted_url(): void
    {
        $invoice = $this->createTestInvoice(200.00);

        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/bills' => Http::response([
                'id' => 'bill_xyz789',
                'paid' => false,
                'amount' => 20000,
                'url' => 'https://www.billplz-sandbox.com/bills/bill_xyz789',
            ], 200),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('billing.billplz.checkout', $invoice));

        $response->assertRedirect('https://www.billplz-sandbox.com/bills/bill_xyz789');

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'billplz_bill_id' => 'bill_xyz789',
            'payment_method' => 'fpx',
            'status' => PaymentStatus::Pending,
        ]);
    }

    /**
     * Test valid X-Signature webhook marks payment successful and invoice paid.
     */
    public function test_webhook_with_valid_x_signature_marks_payment_successful_and_invoice_paid(): void
    {
        $invoice = $this->createTestInvoice(100.00);

        $payment = Payment::create([
            'user_id' => $this->studentUser->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100.00,
            'payment_method' => 'fpx',
            'description' => 'FPX Online Payment Test',
            'status' => PaymentStatus::Pending,
            'billplz_bill_id' => 'bill_webhook_test_1',
            'billplz_url' => 'https://www.billplz-sandbox.com/bills/bill_webhook_test_1',
        ]);

        PaymentInvoice::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount_applied' => 100.00,
        ]);

        $payload = [
            'amount' => '10000',
            'id' => 'bill_webhook_test_1',
            'paid' => 'true',
            'paid_amount' => '10000',
            'paid_at' => now()->toDateTimeString(),
            'state' => 'paid',
        ];

        $signature = $this->billplzService->generateSignature($payload, $this->xSignatureKey);
        $payload['x_signature'] = $signature;

        $response = $this->post(route('billing.billplz.webhook'), $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals(PaymentStatus::Successful, $payment->status);
        $this->assertNotNull($payment->receipt_number);
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertEquals(100.00, (float) $invoice->amount_paid);
    }

    /**
     * Test webhook with invalid X-Signature is rejected with 400.
     */
    public function test_webhook_with_invalid_x_signature_is_rejected(): void
    {
        $invoice = $this->createTestInvoice(100.00);

        $payment = Payment::create([
            'user_id' => $this->studentUser->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 100.00,
            'payment_method' => 'fpx',
            'description' => 'FPX Online Payment Test',
            'status' => PaymentStatus::Pending,
            'billplz_bill_id' => 'bill_tampered_sig',
        ]);

        $payload = [
            'amount' => '10000',
            'id' => 'bill_tampered_sig',
            'paid' => 'true',
            'paid_at' => now()->toDateTimeString(),
            'state' => 'paid',
            'x_signature' => 'invalid_tampered_hash_value',
        ];

        $response = $this->post(route('billing.billplz.webhook'), $payload);

        $response->assertStatus(400);

        $payment->refresh();
        $this->assertEquals(PaymentStatus::Pending, $payment->status);
    }

    /**
     * Test idempotency prevents double receipt generation on duplicate webhooks.
     */
    public function test_webhook_idempotency_prevents_duplicate_receipts(): void
    {
        $invoice = $this->createTestInvoice(120.00);

        $payment = Payment::create([
            'user_id' => $this->studentUser->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 120.00,
            'payment_method' => 'fpx',
            'description' => 'FPX Online Payment Test',
            'status' => PaymentStatus::Pending,
            'billplz_bill_id' => 'bill_idempotent_test',
        ]);

        PaymentInvoice::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount_applied' => 120.00,
        ]);

        $payload = [
            'amount' => '12000',
            'id' => 'bill_idempotent_test',
            'paid' => 'true',
            'paid_amount' => '12000',
            'paid_at' => now()->toDateTimeString(),
            'state' => 'paid',
        ];
        $payload['x_signature'] = $this->billplzService->generateSignature($payload, $this->xSignatureKey);

        // First webhook delivery
        $this->post(route('billing.billplz.webhook'), $payload)->assertOk();
        $payment->refresh();
        $firstReceipt = $payment->receipt_number;
        $this->assertNotNull($firstReceipt);

        // Duplicate delivery of same webhook
        $this->post(route('billing.billplz.webhook'), $payload)->assertOk();
        $payment->refresh();

        $this->assertEquals($firstReceipt, $payment->receipt_number);
        // Only 1 payment record should exist
        $this->assertEquals(1, Payment::where('billplz_bill_id', 'bill_idempotent_test')->count());
    }

    /**
     * Test return redirect handler completes payment and redirects user.
     */
    public function test_return_callback_handles_successful_payment_redirect(): void
    {
        $invoice = $this->createTestInvoice(80.00);

        $payment = Payment::create([
            'user_id' => $this->studentUser->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 80.00,
            'payment_method' => 'fpx',
            'description' => 'FPX Online Payment Test',
            'status' => PaymentStatus::Pending,
            'billplz_bill_id' => 'bill_return_test_1',
        ]);

        PaymentInvoice::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount_applied' => 80.00,
        ]);

        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/bills/bill_return_test_1' => Http::response([
                'id' => 'bill_return_test_1',
                'paid' => true,
                'paid_amount' => 8000,
                'state' => 'paid',
            ], 200),
        ]);

        $redirect = ['billplz' => [
            'id' => 'bill_return_test_1',
            'paid' => 'true',
            'paid_at' => now()->format('Y-m-d H:i:s O'),
        ]];
        $redirect['billplz']['x_signature'] = $this->billplzService->generateSignature($redirect, $this->xSignatureKey);

        $response = $this->actingAs($this->studentUser)
            ->get(route('billing.billplz.return', $redirect));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::Successful, $payment->status);
    }

    private function pendingPayment(string $billId, float $amount): Payment
    {
        $invoice = $this->createTestInvoice($amount);
        $payment = Payment::create([
            'user_id' => $this->studentUser->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => $amount,
            'payment_method' => 'fpx',
            'description' => 'FPX Online Payment Test',
            'status' => PaymentStatus::Pending,
            'billplz_bill_id' => $billId,
        ]);
        PaymentInvoice::create(['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'amount_applied' => $amount]);

        return $payment;
    }

    /**
     * The return URL is a normal browser link: a hand-made "paid=true" link
     * must not settle (or fail) anything without a valid X-Signature.
     */
    public function test_forged_unsigned_return_redirect_does_not_change_payment(): void
    {
        $payment = $this->pendingPayment('bill_forged_return', 80.00);

        $this->actingAs($this->studentUser)
            ->get(route('billing.billplz.return', ['billplz' => ['id' => 'bill_forged_return', 'paid' => 'true']]))
            ->assertRedirect()
            ->assertSessionHas('info');

        $this->assertEquals(PaymentStatus::Pending, $payment->refresh()->status);
        $this->assertNull($payment->receipt_number);

        // A forged "paid=false" link must not cancel someone's pending payment either
        $this->get(route('billing.billplz.return', ['billplz' => ['id' => 'bill_forged_return', 'paid' => 'false', 'x_signature' => 'bogus']]))
            ->assertSessionHas('info');
        $this->assertEquals(PaymentStatus::Pending, $payment->refresh()->status);
    }

    /**
     * Previously verifySignature() returned true when no key was configured,
     * so any forged callback was accepted. It must now fail closed.
     */
    public function test_webhook_is_rejected_when_no_x_signature_key_is_configured(): void
    {
        $payment = $this->pendingPayment('bill_no_key', 100.00);
        Setting::set('billplz_x_signature_key', '', 'finance');
        config(['services.billplz.x_signature_key' => null]);

        $payload = ['id' => 'bill_no_key', 'paid' => 'true', 'paid_amount' => '10000', 'state' => 'paid'];
        $payload['x_signature'] = $this->billplzService->generateSignature($payload, 'attacker-chosen-key');

        $this->post(route('billing.billplz.webhook'), $payload)->assertStatus(400);
        $this->assertEquals(PaymentStatus::Pending, $payment->refresh()->status);
    }

    /**
     * A genuine, correctly signed callback whose paid amount differs from
     * what was billed is acknowledged (so Billplz stops retrying) but left
     * pending and flagged, never settling the invoice.
     */
    public function test_signed_webhook_with_wrong_paid_amount_is_flagged_not_settled(): void
    {
        $payment = $this->pendingPayment('bill_amount_mismatch', 100.00);

        $payload = ['amount' => '10000', 'id' => 'bill_amount_mismatch', 'paid' => 'true', 'paid_amount' => '100', 'state' => 'paid'];
        $payload['x_signature'] = $this->billplzService->generateSignature($payload, $this->xSignatureKey);

        $this->post(route('billing.billplz.webhook'), $payload)->assertOk();

        $payment->refresh();
        $this->assertEquals(PaymentStatus::Pending, $payment->status);
        $this->assertNull($payment->receipt_number);
        $this->assertStringContainsString('Needs review', (string) $payment->payment_proof_notes);
        $this->assertEquals(0.0, (float) $payment->invoice->refresh()->amount_paid);
    }

    /**
     * An unpaid callback without a "state" field used to throw (undefined
     * index), answering 500 and making Billplz retry and degrade the account.
     */
    public function test_signed_unpaid_webhook_without_state_marks_payment_failed(): void
    {
        $payment = $this->pendingPayment('bill_unpaid_no_state', 50.00);

        $payload = ['id' => 'bill_unpaid_no_state', 'paid' => 'false'];
        $payload['x_signature'] = $this->billplzService->generateSignature($payload, $this->xSignatureKey);

        $this->post(route('billing.billplz.webhook'), $payload)->assertOk();
        $this->assertEquals(PaymentStatus::Failed, $payment->refresh()->status);
    }

    /**
     * Callback and redirect for the same bill (Billplz does not order them)
     * settle it once: one receipt, invoice paid once.
     */
    public function test_callback_then_redirect_for_same_bill_settles_once(): void
    {
        $payment = $this->pendingPayment('bill_both_channels', 60.00);

        $callback = ['amount' => '6000', 'id' => 'bill_both_channels', 'paid' => 'true', 'paid_amount' => '6000', 'state' => 'paid'];
        $callback['x_signature'] = $this->billplzService->generateSignature($callback, $this->xSignatureKey);
        $this->post(route('billing.billplz.webhook'), $callback)->assertOk();
        $receipt = $payment->refresh()->receipt_number;

        $redirect = ['billplz' => ['id' => 'bill_both_channels', 'paid' => 'true', 'paid_at' => now()->format('Y-m-d H:i:s O')]];
        $redirect['billplz']['x_signature'] = $this->billplzService->generateSignature($redirect, $this->xSignatureKey);
        $this->actingAs($this->studentUser)->get(route('billing.billplz.return', $redirect))->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame($receipt, $payment->receipt_number);
        $this->assertEquals(60.00, (float) $payment->invoice->refresh()->amount_paid);
    }

    /**
     * Test admin connection test endpoint.
     */
    public function test_admin_can_test_billplz_connection(): void
    {
        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/collections/'.$this->collectionId => Http::response([
                'id' => $this->collectionId,
                'title' => 'Olympian Scholars Fees',
                'status' => 'active',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.billplz.test'), [
                'api_key' => $this->apiKey,
                'collection_id' => $this->collectionId,
                'sandbox' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'collection_title' => 'Olympian Scholars Fees',
            ]);
    }
}
