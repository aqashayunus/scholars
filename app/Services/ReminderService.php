<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    /**
     * Dispatch all due reminders that have not been sent yet.
     *
     * This is called daily by the scheduler command.
     * Logic:
     *   1. Find all pending InvoiceReminder records where scheduled_at <= today
     *   2. Skip if the invoice is already PAID or CANCELLED
     *   3. Skip if the reminder setting is disabled by Admin
     *   4. Dispatch a queued SendInvoiceReminderJob for each
     *
     * The SendInvoiceReminderJob performs a final idempotency check before sending.
     */
    public function dispatchDueReminders(): int
    {
        $today = now()->startOfDay();

        $reminders = InvoiceReminder::with(['invoice'])
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', $today)
            ->get();

        $dispatched = 0;

        foreach ($reminders as $reminder) {
            $invoice = $reminder->invoice;

            // Guard: skip if invoice is paid or cancelled
            if (!$invoice || in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
                $reminder->update(['status' => 'skipped']);
                continue;
            }

            // Guard: skip if admin has disabled this reminder type
            $settingKey = $this->settingKeyForType($reminder->reminder_type);
            if ($settingKey && !Setting::get($settingKey, '1')) {
                $reminder->update(['status' => 'skipped']);
                continue;
            }

            // Dispatch to queue — the job itself will re-check invoice status before sending
            \App\Jobs\SendInvoiceReminderJob::dispatch($reminder)->onQueue('emails');

            $dispatched++;
        }

        Log::info("[ReminderService] Dispatched {$dispatched} reminder job(s).");

        return $dispatched;
    }

    /**
     * Skip (cancel) all pending reminders for a fully-paid or cancelled invoice.
     * Called immediately when an invoice transitions to PAID or CANCELLED.
     */
    public function skipRemainingReminders(Invoice $invoice): int
    {
        $count = InvoiceReminder::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->update([
                'status'  => 'skipped',
                'sent_at' => now(),
            ]);

        if ($count > 0) {
            Log::info("[ReminderService] Skipped {$count} pending reminder(s) for invoice #{$invoice->id} ({$invoice->invoice_number}) — invoice is {$invoice->status}.");
        }

        return $count;
    }

    /**
     * Map a reminder_type to its admin settings key.
     */
    private function settingKeyForType(string $type): ?string
    {
        return match ($type) {
            'invoice_issued'  => 'billing.reminder_invoice_issued',
            'due_7_days'      => 'billing.reminder_due_7_days',
            'due_3_days'      => 'billing.reminder_due_3_days',
            'due_today'       => 'billing.reminder_due_today',
            'overdue_1_day'   => 'billing.reminder_overdue_1_day',
            'overdue_3_days'  => 'billing.reminder_overdue_3_days',
            'overdue_7_days'  => 'billing.reminder_overdue_7_days',
            default           => null,
        };
    }
}
