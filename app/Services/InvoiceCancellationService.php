<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cancelling unpaid invoices safely: only invoices with no money on them and
 * no payment waiting or recorded. Cancelling stops the pending reminders and
 * writes an audit log entry. No email is sent to the family.
 *
 * Used by the Financials "Cancel unpaid invoices" action (free trial,
 * 2026-12) and the admin Level Clean-up.
 */
class InvoiceCancellationService
{
    public function __construct(private ReminderService $reminderService) {}

    /** Why this invoice cannot be cancelled automatically, or null when it can. */
    public function blocker(Invoice $invoice): ?string
    {
        if (in_array($invoice->status, [InvoiceStatus::Cancelled, InvoiceStatus::Draft], true)) {
            return "{$invoice->invoice_number} is {$invoice->status->label()}.";
        }
        if ($invoice->status === InvoiceStatus::Paid || (float) $invoice->amount_paid > 0) {
            return "Invoice {$invoice->invoice_number} has money paid on it. Sort it out in Billing first.";
        }
        if ($this->hasLivePayment($invoice)) {
            return "Invoice {$invoice->invoice_number} has a payment waiting or recorded. Sort it out in Billing first.";
        }

        return null;
    }

    /**
     * Unpaid invoices that can be cancelled now.
     *
     * @return Collection<int, Invoice>
     */
    public function cancellable(): Collection
    {
        return Invoice::with('student.user')
            ->whereIn('status', array_map(fn ($s) => $s->value, InvoiceStatus::unpaidStatuses()))
            ->orderBy('id')
            ->get()
            ->filter(fn (Invoice $i) => $this->blocker($i) === null)
            ->values();
    }

    /**
     * Cancel one invoice. Call blocker() first; this re-checks and throws.
     *
     * @param  array<string, mixed>  $auditExtra
     */
    public function cancel(Invoice $invoice, string $reason, ?int $adminUserId, array $auditExtra = []): void
    {
        if ($msg = $this->blocker($invoice)) {
            throw new \RuntimeException($msg);
        }

        $invoice->status = InvoiceStatus::Cancelled;
        $invoice->notes = trim(($invoice->notes ? $invoice->notes."\n" : '')."Cancelled: {$reason}");
        $invoice->save();
        $this->reminderService->skipRemainingReminders($invoice);

        // financial_audit_logs.user_id is a required FK. System jobs (no admin)
        // are logged against the student's own user, marked actor=system, the
        // same way InvoiceService logs invoice_issued.
        $userId = $adminUserId ?? $invoice->student?->user_id;
        if ($userId === null) {
            Log::warning("[InvoiceCancellation] {$invoice->invoice_number} cancelled ({$reason}); no user to audit-log against.");

            return;
        }
        try {
            FinancialAuditLog::create([
                'user_id' => $userId,
                'action' => 'invoice_cancelled',
                'target_type' => 'Invoice',
                'target_id' => $invoice->id,
                'ip_address' => request()->ip(),
                'details' => json_encode(array_merge(
                    ['invoice_number' => $invoice->invoice_number, 'reason' => $reason, 'actor' => $adminUserId ? 'admin' : 'system'],
                    $auditExtra
                )),
            ]);
        } catch (\Throwable $e) {
            Log::error("[InvoiceCancellation] Audit log failed for {$invoice->invoice_number}: ".$e->getMessage());
        }
    }

    /**
     * Cancel the given invoices. Ones that became uncancellable since the
     * admin looked are skipped, not cancelled.
     *
     * @param  list<int>  $invoiceIds
     * @return array{cancelled: list<string>, skipped: list<string>}
     */
    public function cancelMany(array $invoiceIds, string $reason, ?int $adminUserId): array
    {
        $cancelled = [];
        $skipped = [];

        DB::transaction(function () use ($invoiceIds, $reason, $adminUserId, &$cancelled, &$skipped) {
            $invoices = Invoice::whereIn('id', $invoiceIds)->orderBy('id')->lockForUpdate()->get();
            foreach ($invoices as $invoice) {
                if ($msg = $this->blocker($invoice)) {
                    $skipped[] = $msg;

                    continue;
                }
                $this->cancel($invoice, $reason, $adminUserId, ['bulk' => true]);
                $cancelled[] = $invoice->invoice_number;
            }
        });

        return ['cancelled' => $cancelled, 'skipped' => $skipped];
    }

    public function hasLivePayment(Invoice $invoice): bool
    {
        return Payment::where(function ($q) use ($invoice) {
            $q->where('invoice_id', $invoice->id)
                ->orWhereHas('invoices', fn ($i) => $i->where('invoices.id', $invoice->id));
        })
            ->whereNotIn('status', [PaymentStatus::Failed->value, PaymentStatus::Cancelled->value])
            ->exists();
    }
}
