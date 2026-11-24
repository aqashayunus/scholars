<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\User;
use App\Services\Billplz\BillplzService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private BillplzService $billplzService
    ) {}

    // ── Manual Bank Transfer ──────────────────────────────────────────────────

    /**
     * Initiate a manual bank transfer payment for one or more invoices.
     *
     * Creates a pending Payment record and PaymentInvoice allocations.
     * Status = pending until Admin manually verifies and approves.
     *
     * @param  int[]  $invoiceIds  Invoice IDs to pay (can be multiple for multi-child checkout)
     * @param  User  $payer  The user initiating payment (student or parent)
     * @param  array  $data  Optional extra fields: proof_file_path, reference_no, description
     */
    public function initiateManualTransfer(array $invoiceIds, User $payer, array $data = []): Payment
    {
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->whereIn('status', InvoiceStatus::unpaidStatuses())
            ->get();

        if ($invoices->isEmpty()) {
            throw new \RuntimeException('No valid unpaid invoices found for the given IDs.');
        }

        // Total amount = sum of outstanding amounts per invoice
        $totalAmount = $invoices->sum(fn ($inv) => $inv->outstanding_amount);

        return DB::transaction(function () use ($invoices, $payer, $totalAmount, $data) {
            // Determine payer type
            $payerType = 'student';
            if ($payer->role === 'parent') {
                $payerType = 'parent';
            } elseif ($payer->role === 'admin') {
                $payerType = 'admin';
            }

            // Get the primary student from the first invoice
            $primaryStudentId = $invoices->first()->student_id;

            $payment = Payment::create([
                'user_id' => $payer->id,
                'student_id' => $primaryStudentId,
                'payer_id' => $payer->id,
                'payer_type' => $payerType,
                'description' => $data['description'] ?? 'Manual Bank Transfer – '.$invoices->pluck('invoice_number')->implode(', '),
                'amount' => $totalAmount,
                'payment_method' => 'bank_transfer',
                'proof_file_path' => $data['proof_file_path'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'status' => PaymentStatus::Pending,
            ]);

            // Create payment_invoices allocations
            foreach ($invoices as $invoice) {
                PaymentInvoice::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $invoice->outstanding_amount,
                ]);
            }

            // Audit log
            $this->log('payment_initiated', 'Payment', $payment->id, $payer->id, [
                'amount' => $totalAmount,
                'payment_method' => 'bank_transfer',
                'invoices' => $invoices->pluck('invoice_number')->all(),
            ]);

            return $payment->fresh(['invoices']);
        });
    }

    // ── Admin: Approve Manual Payment ─────────────────────────────────────────

    /**
     * Admin approves a manual bank transfer payment.
     * Marks payment as successful, generates receipt, updates each invoice.
     */
    public function approveManualPayment(Payment $payment, User $admin, ?string $notes = null): void
    {
        if ($payment->status !== PaymentStatus::Pending) {
            throw new \RuntimeException("Payment #{$payment->id} is not in pending status.");
        }

        DB::transaction(function () use ($payment, $admin, $notes) {
            $receiptNumber = Payment::generateReceiptNumber();

            $payment->update([
                'status' => PaymentStatus::Successful,
                'receipt_number' => $receiptNumber,
                'verified_by' => $admin->id,
                'payment_proof_notes' => $notes,
                'paid_at' => now(),
            ]);

            // Recalculate each linked invoice
            $allocations = $payment->paymentInvoices()->with('invoice')->get();

            foreach ($allocations as $allocation) {
                $this->invoiceService->recalculateAmountPaid($allocation->invoice);
            }

            $this->log('payment_approved', 'Payment', $payment->id, $admin->id, [
                'receipt_number' => $receiptNumber,
                'approved_by' => $admin->email,
            ]);
        });

        // In-app alert for student & parents
        BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), true, $notes);
    }

    // ── Admin: Reject Manual Payment ──────────────────────────────────────────

    /**
     * Admin rejects a manual bank transfer payment.
     */
    public function rejectManualPayment(Payment $payment, User $admin, string $reason): void
    {
        if ($payment->status !== PaymentStatus::Pending) {
            throw new \RuntimeException("Payment #{$payment->id} is not in pending status.");
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'verified_by' => $admin->id,
            'payment_proof_notes' => "Rejected: {$reason}",
        ]);

        $this->log('payment_rejected', 'Payment', $payment->id, $admin->id, [
            'reason' => $reason,
            'rejected_by' => $admin->email,
        ]);

        // In-app alert for student & parents
        BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), false, $reason);
    }

    // ── Upload Payment Proof ──────────────────────────────────────────────────

    /**
     * Handle proof-of-payment file upload from parent/student.
     * Stores securely and updates the Payment record.
     */
    public function uploadProof(Payment $payment, UploadedFile $file): string
    {
        $path = $file->store('payment-proofs', 'public');

        $payment->update(['proof_file_path' => $path]);

        $this->log('proof_uploaded', 'Payment', $payment->id, null, [
            'file' => basename($path),
        ]);

        $inv = $payment->invoice ?? $payment->invoices()->first();
        if ($inv && $payment->user) {
            BillingNotificationService::notifyBankTransferSubmitted($inv, $payment, $payment->user);
        }

        return $path;
    }

    // ── Billplz FPX Gateway Integration ────────────────────────────────────────

    /**
     * Initiate a Billplz FPX online payment for one or more invoices.
     *
     * @param  int[]  $invoiceIds
     * @return array ['payment' => Payment, 'bill_url' => string]
     */
    public function initiateBillplzPayment(array $invoiceIds, User $payer, array $extra = []): array
    {
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->whereIn('status', InvoiceStatus::unpaidStatuses())
            ->get();

        if ($invoices->isEmpty()) {
            throw new \RuntimeException('No valid unpaid invoices found to pay.');
        }

        $totalAmount = $invoices->sum(fn ($inv) => (float) $inv->outstanding_amount);

        if ($totalAmount <= 0) {
            throw new \RuntimeException('The total payable amount must be greater than zero.');
        }

        return DB::transaction(function () use ($invoices, $payer, $totalAmount) {
            $payerType = $payer->role === 'parent' ? 'parent' : 'student';
            $primaryStudentId = $invoices->first()->student_id;
            $refNo = 'TXN-BPZ-'.strtoupper(substr(md5(uniqid()), 0, 8));

            $payment = Payment::create([
                'user_id' => $payer->id,
                'invoice_id' => $invoices->count() === 1 ? $invoices->first()->id : null,
                'student_id' => $primaryStudentId,
                'payer_id' => $payer->id,
                'payer_type' => $payerType,
                'description' => 'FPX Online Payment – '.$invoices->pluck('invoice_number')->implode(', '),
                'amount' => $totalAmount,
                'payment_method' => 'fpx',
                'reference_no' => $refNo,
                'status' => PaymentStatus::Pending,
            ]);

            // Create allocations
            foreach ($invoices as $invoice) {
                PaymentInvoice::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $invoice->outstanding_amount,
                ]);
            }

            // Prepare Billplz creation payload
            $callbackUrl = route('billing.billplz.webhook');
            $redirectUrl = route('billing.billplz.return');

            $bill = $this->billplzService->createBill([
                'email' => $payer->email,
                'mobile' => $payer->phone ?? $payer->mobile ?? null,
                'name' => $payer->name,
                'amount' => $totalAmount,
                'callback_url' => $callbackUrl,
                'redirect_url' => $redirectUrl,
                'description' => 'Olympian Scholars Fees: '.$invoices->pluck('invoice_number')->implode(', '),
                'reference_1' => (string) $invoices->first()->id,
                'reference_2' => $payment->reference_no,
            ]);

            $payment->update([
                'billplz_bill_id' => $bill['id'] ?? null,
                'gateway_transaction_id' => $bill['id'] ?? null,
                'billplz_url' => $bill['url'] ?? null,
            ]);

            $this->log('billplz_payment_initiated', 'Payment', $payment->id, $payer->id, [
                'amount' => $totalAmount,
                'billplz_bill_id' => $bill['id'] ?? null,
                'invoices' => $invoices->pluck('invoice_number')->all(),
            ]);

            return [
                'payment' => $payment->fresh(['invoices']),
                'bill_url' => $bill['url'],
            ];
        });
    }

    /**
     * Mark a Billplz gateway payment as successful and reconcile linked invoices.
     *
     * Callers must have verified the X-Signature first. Strictly idempotent
     * and safe when the callback and the redirect arrive at the same moment
     * (Billplz does not order them): the payment row is locked and its status
     * re-checked inside the transaction, so only one of them settles it.
     *
     * When the gateway reports a paid amount (the callback does; the redirect
     * does not) it must equal the billed amount, otherwise the payment is left
     * pending and flagged for admin review instead of settling the invoices.
     *
     * @param  array<mixed>  $gatewayData
     */
    public function completeGatewayPayment(string $billplzBillId, array $gatewayData = [], ?int $paidAmountCents = null): Payment
    {
        $payment = Payment::where('billplz_bill_id', $billplzBillId)
            ->orWhere('gateway_transaction_id', $billplzBillId)
            ->first();

        if (! $payment) {
            throw new \RuntimeException("Payment not found for Billplz Bill ID: {$billplzBillId}");
        }

        $expectedCents = (int) round(((float) $payment->amount) * 100);
        if ($paidAmountCents !== null && $paidAmountCents !== $expectedCents) {
            Log::critical("[PaymentService] Billplz paid amount mismatch for payment #{$payment->id}: billed {$expectedCents} cents, gateway reported {$paidAmountCents} cents. Left pending for review.", $gatewayData);
            $payment->update([
                'gateway_payload' => $gatewayData,
                'payment_proof_notes' => 'Needs review: Billplz reported RM '.number_format($paidAmountCents / 100, 2).' paid, but RM '.number_format($expectedCents / 100, 2).' was billed.',
            ]);
            $this->log('billplz_amount_mismatch', 'Payment', $payment->id, $payment->user_id, [
                'billplz_bill_id' => $billplzBillId,
                'billed_cents' => $expectedCents,
                'reported_cents' => $paidAmountCents,
            ]);

            return $payment;
        }

        $settled = DB::transaction(function () use ($payment, $gatewayData) {
            $locked = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            // Idempotency: already settled (by a duplicate or the other channel)
            if ($locked->status === PaymentStatus::Successful) {
                Log::info("[PaymentService] Payment #{$locked->id} already marked successful.");

                return false;
            }

            $receiptNumber = $locked->receipt_number ?: Payment::generateReceiptNumber();

            $locked->update([
                'status' => PaymentStatus::Successful,
                'receipt_number' => $receiptNumber,
                'gateway_payload' => $gatewayData,
                'paid_at' => now(),
            ]);

            // Recalculate each linked invoice
            $allocations = $locked->paymentInvoices()->with('invoice')->get();
            if ($allocations->isNotEmpty()) {
                foreach ($allocations as $allocation) {
                    if ($allocation->invoice) {
                        $this->invoiceService->recalculateAmountPaid($allocation->invoice);
                    }
                }
            } elseif ($locked->invoice_id && $locked->invoice) {
                $this->invoiceService->recalculateAmountPaid($locked->invoice);
            }

            $this->log('billplz_payment_successful', 'Payment', $locked->id, $locked->user_id, [
                'receipt_number' => $receiptNumber,
                'billplz_bill_id' => $locked->billplz_bill_id,
                'amount' => $locked->amount,
            ]);

            return true;
        });

        $payment->refresh();

        if (! $settled) {
            return $payment;
        }

        // Trigger in-app alerts for student & parents (only from the call that settled it)
        try {
            $freshPayment = $payment->fresh(['invoices.student.user']);
            $firstInvoice = $freshPayment->invoices->first();
            if ($firstInvoice) {
                BillingNotificationService::notifyPaymentVerified($freshPayment, true, 'Paid via Billplz FPX Online Banking');
            }
        } catch (\Throwable $e) {
            Log::warning('[PaymentService] Failed sending payment notification: '.$e->getMessage());
        }

        return $payment;
    }

    /**
     * Mark a Billplz gateway payment as failed.
     */
    public function failGatewayPayment(string $billplzBillId, array $gatewayData = [], ?string $reason = null): Payment
    {
        $payment = Payment::where('billplz_bill_id', $billplzBillId)
            ->orWhere('gateway_transaction_id', $billplzBillId)
            ->first();

        if (! $payment) {
            throw new \RuntimeException("Payment not found for Billplz Bill ID: {$billplzBillId}");
        }

        if ($payment->status === PaymentStatus::Successful) {
            return $payment; // Do not fail an already successful payment
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'gateway_payload' => $gatewayData,
            'payment_proof_notes' => $reason ? "Gateway Failure: {$reason}" : 'Payment cancelled or failed at bank.',
        ]);

        $this->log('billplz_payment_failed', 'Payment', $payment->id, $payment->user_id, [
            'reason' => $reason,
            'billplz_bill_id' => $billplzBillId,
        ]);

        return $payment;
    }

    /**
     * Handle payment gateway webhook callback.
     *
     * @param  array  $payload  Raw webhook payload from Billplz
     */
    public function handleGatewayWebhook(array $payload): void
    {
        Log::info('[PaymentService] Gateway webhook received.', $payload);

        $billId = $payload['id'] ?? null;
        if (empty($billId)) {
            throw new \InvalidArgumentException('Missing bill ID in webhook payload.');
        }

        // Fails closed: an unsigned, mis-signed, or unverifiable (no key
        // configured) callback is rejected before anything is touched.
        if (! $this->billplzService->verifySignature($payload)) {
            Log::error('[PaymentService] Invalid Billplz webhook X-Signature.', $payload);
            throw new \InvalidArgumentException('Invalid webhook signature.');
        }

        $paid = $payload['paid'] ?? null;
        $isPaid = $paid === true || $paid === 'true' || ($payload['state'] ?? null) === 'paid';

        if ($isPaid) {
            $this->completeGatewayPayment($billId, $payload, $this->paidAmountInCents($payload));
        } else {
            $this->failGatewayPayment($billId, $payload, 'Payment cancelled or failed at FPX checkout.');
        }
    }

    /**
     * Amount Billplz reports as paid, in cents (callback "paid_amount",
     * falling back to the bill "amount"); null when the payload has neither
     * (the redirect carries no amount).
     *
     * @param  array<mixed>  $payload
     */
    private function paidAmountInCents(array $payload): ?int
    {
        $raw = $payload['paid_amount'] ?? $payload['amount'] ?? null;

        return is_numeric($raw) ? (int) round((float) $raw) : null;
    }

    // ── Audit Log ─────────────────────────────────────────────────────────────

    private function log(string $action, string $targetType, int $targetId, ?int $userId, array $details = []): void
    {
        try {
            FinancialAuditLog::create([
                'user_id' => $userId ?? 0,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'ip_address' => request()->ip(),
                'details' => json_encode($details),
            ]);
        } catch (\Exception $e) {
            Log::error('[PaymentService] Audit log failed: '.$e->getMessage());
        }
    }
}
