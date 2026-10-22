<?php

namespace Tests\Feature;

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

        $this->billplzService = new BillplzService();

        Setting::set('billplz_api_key', $this->apiKey, 'finance');
        Setting::set('billplz_x_signature_key', $this->xSignatureKey, 'finance');
        Setting::set('billplz_collection_id', $this->collectionId, 'finance');
        Setting::set('billplz_sandbox', '1', 'finance');

        $this->adminUser = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role'      => 'student',
            'is_active' => true,
            'email'     => 'student@olympianscholars.com',
            'name'      => 'Ahmad Student',
        ]);

        $this->student = Student::firstOrCreate(
            ['user_id' => $this->studentUser->id],
            [
                'parent_id'  => null,
                'level'      => 'Form 4',
                'birth_date' => '2008-01-01',
                'school'     => 'SMK Olympian',
                'gender'     => 'Male',
            ]
        );
    }

    private function createTestInvoice(float $total = 150.00, string $status = Invoice::STATUS_ISSUED): Invoice
    {
        return Invoice::create([
            'student_id'      => $this->student->id,
            'invoice_number'  => 'INV-' . strtoupper(uniqid()),
            'title'           => 'Monthly Tuition Fee - Test',
            'description'     => 'Standard secondary tuition package',
            'billing_period'  => now()->format('F Y'),
            'subtotal'        => $total,
            'discount_amount' => 0.00,
            'total_amount'    => $total,
            'amount_paid'     => 0.00,
            'issued_date'     => now()->toDateString(),
            'due_date'        => now()->addDays(7)->toDateString(),
            'status'          => $status,
        ]);
    }

    /**
     * Test cents calculation and API payload when creating a Billplz bill.
     */
    public function test_billplz_bill_creation_and_cents_calculation(): void
    {
        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/bills' => Http::response([
                'id'            => 'bill_abc123',
                'collection_id' => $this->collectionId,
                'paid'          => false,
                'state'         => 'due',
                'amount'        => 15050,
                'url'           => 'https://www.billplz-sandbox.com/bills/bill_abc123',
            ], 200),
        ]);

        $bill = $this->billplzService->createBill([
            'collection_id' => $this->collectionId,
            'email'         => 'parent@example.com',
            'name'          => 'Parent Ali',
            'amount'        => 150.50, // RM 150.50 should convert to 15050 cents
            'callback_url'  => 'https://example.com/webhook',
            'description'   => 'Test Tuition Payment',
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
                'id'     => 'bill_xyz789',
                'paid'   => false,
                'amount' => 20000,
                'url'    => 'https://www.billplz-sandbox.com/bills/bill_xyz789',
            ], 200),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('billing.billplz.checkout', $invoice));

        $response->assertRedirect('https://www.billplz-sandbox.com/bills/bill_xyz789');

        $this->assertDatabaseHas('payments', [
            'invoice_id'      => $invoice->id,
            'billplz_bill_id' => 'bill_xyz789',
            'payment_method'  => 'fpx',
            'status'          => Payment::STATUS_PENDING,
        ]);
    }

    /**
     * Test valid X-Signature webhook marks payment successful and invoice paid.
     */
    public function test_webhook_with_valid_x_signature_marks_payment_successful_and_invoice_paid(): void
    {
        $invoice = $this->createTestInvoice(100.00);

        $payment = Payment::create([
            'user_id'         => $this->studentUser->id,
            'invoice_id'      => $invoice->id,
            'student_id'      => $this->student->id,
            'amount'          => 100.00,
            'payment_method'  => 'fpx',
            'description'     => 'FPX Online Payment Test',
            'status'          => Payment::STATUS_PENDING,
            'billplz_bill_id' => 'bill_webhook_test_1',
            'billplz_url'     => 'https://www.billplz-sandbox.com/bills/bill_webhook_test_1',
        ]);

        PaymentInvoice::create([
            'payment_id'     => $payment->id,
            'invoice_id'     => $invoice->id,
            'amount_applied' => 100.00,
        ]);

        $payload = [
            'amount'          => '10000',
            'id'              => 'bill_webhook_test_1',
            'paid'            => 'true',
            'paid_amount'     => '10000',
            'paid_at'         => now()->toDateTimeString(),
            'state'           => 'paid',
        ];

        $signature = $this->billplzService->generateSignature($payload, $this->xSignatureKey);
        $payload['x_signature'] = $signature;

        $response = $this->post(route('billing.billplz.webhook'), $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals(Payment::STATUS_SUCCESSFUL, $payment->status);
        $this->assertNotNull($payment->receipt_number);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertEquals(100.00, (float) $invoice->amount_paid);
    }

    /**
     * Test webhook with invalid X-Signature is rejected with 400.
     */
    public function test_webhook_with_invalid_x_signature_is_rejected(): void
    {
        $invoice = $this->createTestInvoice(100.00);

        $payment = Payment::create([
            'user_id'         => $this->studentUser->id,
            'invoice_id'      => $invoice->id,
            'student_id'      => $this->student->id,
            'amount'          => 100.00,
            'payment_method'  => 'fpx',
            'description'     => 'FPX Online Payment Test',
            'status'          => Payment::STATUS_PENDING,
            'billplz_bill_id' => 'bill_tampered_sig',
        ]);

        $payload = [
            'amount'          => '10000',
            'id'              => 'bill_tampered_sig',
            'paid'            => 'true',
            'paid_at'         => now()->toDateTimeString(),
            'state'           => 'paid',
            'x_signature'     => 'invalid_tampered_hash_value',
        ];

        $response = $this->post(route('billing.billplz.webhook'), $payload);

        $response->assertStatus(400);

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_PENDING, $payment->status);
    }

    /**
     * Test idempotency prevents double receipt generation on duplicate webhooks.
     */
    public function test_webhook_idempotency_prevents_duplicate_receipts(): void
    {
        $invoice = $this->createTestInvoice(120.00);

        $payment = Payment::create([
            'user_id'         => $this->studentUser->id,
            'invoice_id'      => $invoice->id,
            'student_id'      => $this->student->id,
            'amount'          => 120.00,
            'payment_method'  => 'fpx',
            'description'     => 'FPX Online Payment Test',
            'status'          => Payment::STATUS_PENDING,
            'billplz_bill_id' => 'bill_idempotent_test',
        ]);

        PaymentInvoice::create([
            'payment_id'     => $payment->id,
            'invoice_id'     => $invoice->id,
            'amount_applied' => 120.00,
        ]);

        $payload = [
            'amount'          => '12000',
            'id'              => 'bill_idempotent_test',
            'paid'            => 'true',
            'paid_amount'     => '12000',
            'paid_at'         => now()->toDateTimeString(),
            'state'           => 'paid',
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
            'user_id'         => $this->studentUser->id,
            'invoice_id'      => $invoice->id,
            'student_id'      => $this->student->id,
            'amount'          => 80.00,
            'payment_method'  => 'fpx',
            'description'     => 'FPX Online Payment Test',
            'status'          => Payment::STATUS_PENDING,
            'billplz_bill_id' => 'bill_return_test_1',
        ]);

        PaymentInvoice::create([
            'payment_id'     => $payment->id,
            'invoice_id'     => $invoice->id,
            'amount_applied' => 80.00,
        ]);

        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/bills/bill_return_test_1' => Http::response([
                'id'          => 'bill_return_test_1',
                'paid'        => true,
                'paid_amount' => 8000,
                'state'       => 'paid',
            ], 200),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('billing.billplz.return', [
                'billplz' => [
                    'id'   => 'bill_return_test_1',
                    'paid' => 'true',
                ],
            ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_SUCCESSFUL, $payment->status);
    }

    /**
     * Test admin connection test endpoint.
     */
    public function test_admin_can_test_billplz_connection(): void
    {
        Http::fake([
            'https://www.billplz-sandbox.com/api/v3/collections/' . $this->collectionId => Http::response([
                'id'     => $this->collectionId,
                'title'  => 'Olympian Scholars Fees',
                'status' => 'active',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.billplz.test'), [
                'api_key'       => $this->apiKey,
                'collection_id' => $this->collectionId,
                'sandbox'       => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success'          => true,
                'collection_title' => 'Olympian Scholars Fees',
            ]);
    }
}
