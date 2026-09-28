<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying after a failure.
     */
    public int $backoff = 60;

    public function __construct(public InvoiceReminder $reminder) {}

    public function handle(): void
    {
        $reminder = $this->reminder->fresh(['invoice.student.user', 'invoice.student.parents', 'invoice.items']);

        if (!$reminder) {
            Log::warning('[SendInvoiceReminderJob] Reminder not found — skipping.');
            return;
        }

        // Idempotency guard: only send if still pending
        if ($reminder->status !== 'pending') {
            Log::info("[SendInvoiceReminderJob] Reminder #{$reminder->id} already {$reminder->status} — skipping.");
            return;
        }

        $invoice = $reminder->invoice;

        // Critical guard: stop if invoice is now paid or cancelled
        if (!$invoice || in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
            $reminder->update(['status' => 'skipped', 'sent_at' => now()]);
            Log::info("[SendInvoiceReminderJob] Reminder #{$reminder->id} skipped — invoice is {$invoice?->status}.");
            return;
        }

        try {
            $mailable = $this->buildMailable($reminder, $invoice);
            Mail::to($reminder->recipient_email)->send($mailable);

            $reminder->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            // In-app alert for recipient user
            \App\Services\BillingNotificationService::notifyReminderSent($reminder);

            Log::info("[SendInvoiceReminderJob] Sent {$reminder->reminder_type} to {$reminder->recipient_email} for invoice {$invoice->invoice_number}.");

        } catch (\Exception $e) {
            $reminder->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);

            Log::error("[SendInvoiceReminderJob] Failed to send reminder #{$reminder->id}: " . $e->getMessage());

            throw $e; // Allow queue to retry
        }
    }

    private function buildMailable(InvoiceReminder $reminder, Invoice $invoice): \Illuminate\Mail\Mailable
    {
        if ($reminder->reminder_type === 'invoice_issued') {
            return new \App\Mail\InvoiceIssuedMail($invoice, $reminder->recipient_email, $reminder->recipient_type);
        }

        return new \App\Mail\InvoiceReminderMail($invoice, $reminder->reminder_type, $reminder->recipient_email, $reminder->recipient_type);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[SendInvoiceReminderJob] Job permanently failed for reminder #{$this->reminder->id}: " . $exception->getMessage());

        $this->reminder->update([
            'status'        => 'failed',
            'error_message' => substr($exception->getMessage(), 0, 500),
        ]);
    }
}
