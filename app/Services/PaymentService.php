<?php

namespace App\Services;

use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    public function __construct(private InvoiceService $invoiceService) {}

    // ── Manual Bank Transfer ──────────────────────────────────────────────────

    /**
     * Initiate a manual bank transfer payment for one or more invoices.
     *
     * Creates a pending Payment record and PaymentInvoice allocations.
     * Status = pending until Admin manually verifies and approves.
     *
     * @param  int[]  $invoiceIds  Invoice IDs to pay (can be multiple for multi-child checkout)
     * @param  User   $payer       The user initiating payment (student or parent)
     * @param  array  $data        Optional extra fields: proof_file_path, reference_no, description
     * @return Payment
     */
    public function initiateManualTransfer(array $invoiceIds, User $payer, array $data = []): Payment
    {
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->whereIn('status', Invoice::UNPAID_STATUSES)
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
                'user_id'        => $payer->id,
                'student_id'     => $primaryStudentId,
                'payer_id'       => $payer->id,
                'payer_type'     => $payerType,
                'description'    => $data['description'] ?? 'Manual Bank Transfer – ' . $invoices->pluck('invoice_number')->implode(', '),
                'amount'         => $totalAmount,
                'payment_method' => 'bank_transfer',
                'proof_file_path'=> $data['proof_file_path'] ?? null,
                'reference_no'   => $data['reference_no'] ?? null,
                'status'         => Payment::STATUS_PENDING,
            ]);

            // Create payment_invoices allocations
            foreach ($invoices as $invoice) {
                PaymentInvoice::create([
                    'payment_id'     => $payment->id,
                    'invoice_id'     => $invoice->id,
                    'amount_applied' => $invoice->outstanding_amount,
                ]);
            }

            // Audit log
            $this->log('payment_initiated', 'Payment', $payment->id, $payer->id, [
                'amount'         => $totalAmount,
                'payment_method' => 'bank_transfer',
                'invoices'       => $invoices->pluck('invoice_number')->all(),
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
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new \RuntimeException("Payment #{$payment->id} is not in pending status.");
        }

        DB::transaction(function () use ($payment, $admin, $notes) {
            $receiptNumber = Payment::generateReceiptNumber();

            $payment->update([
                'status'               => Payment::STATUS_SUCCESSFUL,
                'receipt_number'       => $receiptNumber,
                'verified_by'          => $admin->id,
                'payment_proof_notes'  => $notes,
                'paid_at'              => now(),
            ]);

            // Recalculate each linked invoice
            $allocations = $payment->paymentInvoices()->with('invoice')->get();

            foreach ($allocations as $allocation) {
                $this->invoiceService->recalculateAmountPaid($allocation->invoice);
            }

            $this->log('payment_approved', 'Payment', $payment->id, $admin->id, [
                'receipt_number' => $receiptNumber,
                'approved_by'    => $admin->email,
            ]);
        });

        // In-app alert for student & parents
        \App\Services\BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), true, $notes);
    }

    // ── Admin: Reject Manual Payment ──────────────────────────────────────────

    /**
     * Admin rejects a manual bank transfer payment.
     */
    public function rejectManualPayment(Payment $payment, User $admin, string $reason): void
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new \RuntimeException("Payment #{$payment->id} is not in pending status.");
        }

        $payment->update([
            'status'              => Payment::STATUS_FAILED,
            'verified_by'         => $admin->id,
            'payment_proof_notes' => "Rejected: {$reason}",
        ]);

        $this->log('payment_rejected', 'Payment', $payment->id, $admin->id, [
            'reason'      => $reason,
            'rejected_by' => $admin->email,
        ]);

        // In-app alert for student & parents
        \App\Services\BillingNotificationService::notifyPaymentVerified($payment->fresh(['invoice.student.user']), false, $reason);
    }

    // ── Upload Payment Proof ──────────────────────────────────────────────────

    /**
     * Handle proof-of-payment file upload from parent/student.
     * Stores securely and updates the Payment record.
     */
    public function uploadProof(Payment $payment, \Illuminate\Http\UploadedFile $file): string
    {
        $path = $file->store('payment-proofs', 'public');

        $payment->update(['proof_file_path' => $path]);

        $this->log('proof_uploaded', 'Payment', $payment->id, null, [
            'file' => basename($path),
        ]);

        $inv = $payment->invoice ?? $payment->invoices()->first();
        if ($inv && $payment->user) {
            \App\Services\BillingNotificationService::notifyBankTransferSubmitted($inv, $payment, $payment->user);
        }

        return $path;
    }

    // ── Gateway Webhook (Stub) ────────────────────────────────────────────────

    /**
     * Handle payment gateway webhook callback.
     *
     * STUB for V1 — the architecture is ready to slot in a real gateway.
     * A real implementation would:
     *   1. Verify the payload signature from the gateway
     *   2. Find the Payment by gateway_transaction_id
     *   3. Mark it successful/failed
     *   4. Call recalculateAmountPaid() on each linked invoice
     *
     * @param  array  $payload  Raw webhook payload from gateway
     */
    public function handleGatewayWebhook(array $payload): void
    {
        Log::info('[PaymentService] Gateway webhook received (stub).', $payload);

        // TODO: implement when real payment gateway is configured
        // Example for future implementation:
        //
        // $gatewayRef = $payload['transaction_id'] ?? null;
        // $payment = Payment::where('gateway_transaction_id', $gatewayRef)->first();
        // if (!$payment) { throw new \RuntimeException("Payment not found for gateway ref: {$gatewayRef}"); }
        //
        // if ($payload['status'] === 'paid') {
        //     $payment->update(['status' => Payment::STATUS_SUCCESSFUL, 'paid_at' => now()]);
        //     foreach ($payment->paymentInvoices as $alloc) {
        //         $this->invoiceService->recalculateAmountPaid($alloc->invoice);
        //     }
        // } else {
        //     $payment->update(['status' => Payment::STATUS_FAILED]);
        // }
    }

    // ── Audit Log ─────────────────────────────────────────────────────────────

    private function log(string $action, string $targetType, int $targetId, ?int $userId, array $details = []): void
    {
        try {
            FinancialAuditLog::create([
                'user_id'     => $userId ?? 0,
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip_address'  => request()->ip(),
                'details'     => json_encode($details),
            ]);
        } catch (\Exception $e) {
            Log::error("[PaymentService] Audit log failed: " . $e->getMessage());
        }
    }
}
