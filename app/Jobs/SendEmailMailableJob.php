<?php

namespace App\Jobs;

use App\Mail\OfficialBrandedMail;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailMailableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // 30 seconds backoff between retries

    public string $recipientEmail;
    public string $subject;
    public string $htmlContent;
    public string $plainTextContent;
    public ?int $emailLogId;
    public ?int $campaignRecipientId;
    public ?string $replyToEmail;
    public ?string $senderName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $recipientEmail,
        string $subject,
        string $htmlContent,
        string $plainTextContent = '',
        ?int $emailLogId = null,
        ?int $campaignRecipientId = null,
        ?string $replyToEmail = null,
        ?string $senderName = null
    ) {
        $this->recipientEmail       = strtolower(trim($recipientEmail));
        $this->subject              = $subject;
        $this->htmlContent          = $htmlContent;
        $this->plainTextContent     = $plainTextContent;
        $this->emailLogId           = $emailLogId;
        $this->campaignRecipientId  = $campaignRecipientId;
        $this->replyToEmail         = $replyToEmail;
        $this->senderName           = $senderName;

        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Check suppression list before sending
        if (EmailSuppression::isSuppressed($this->recipientEmail)) {
            $this->recordFailure('Email address is on the institutional suppression list.');
            return;
        }

        $log = $this->emailLogId ? EmailLog::find($this->emailLogId) : null;

        try {
            // 2. Send via Laravel Mail transport
            $mailable = new OfficialBrandedMail(
                $this->subject,
                $this->htmlContent,
                $this->plainTextContent,
                $this->replyToEmail,
                $this->senderName
            );

            Mail::to($this->recipientEmail)->send($mailable);

            // 3. Update EmailLog on success
            if ($log) {
                $log->update([
                    'status'       => EmailLog::STATUS_SENT,
                    'sent_at'      => now(),
                    'delivered_at' => now(), // confirmed passed to transport
                ]);
            }

            // 4. Update CampaignRecipient if applicable
            if ($this->campaignRecipientId) {
                $recipient = EmailCampaignRecipient::find($this->campaignRecipientId);
                if ($recipient) {
                    $recipient->update([
                        'status'  => EmailCampaignRecipient::STATUS_SENT,
                        'sent_at' => now(),
                    ]);
                    $recipient->campaign?->increment('sent_count');
                }
            }

        } catch (\Throwable $e) {
            $sanitizedError = $this->sanitizeErrorMessage($e->getMessage());
            Log::error("[SendEmailMailableJob] Sending to {$this->recipientEmail} failed: " . $sanitizedError);

            if ($log) {
                $log->increment('attempts');
                $log->update([
                    'status'        => EmailLog::STATUS_FAILED,
                    'error_message' => $sanitizedError,
                ]);
            }

            if ($this->campaignRecipientId) {
                $recipient = EmailCampaignRecipient::find($this->campaignRecipientId);
                if ($recipient) {
                    $recipient->update([
                        'status'        => EmailCampaignRecipient::STATUS_FAILED,
                        'error_message' => $sanitizedError,
                    ]);
                    $recipient->campaign?->increment('failed_count');
                }
            }

            // Re-throw if under retry limit to allow queue retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Record explicit failure without re-trying.
     */
    protected function recordFailure(string $reason): void
    {
        if ($this->emailLogId) {
            EmailLog::where('id', $this->emailLogId)->update([
                'status'        => EmailLog::STATUS_FAILED,
                'error_message' => $reason,
            ]);
        }

        if ($this->campaignRecipientId) {
            $recipient = EmailCampaignRecipient::find($this->campaignRecipientId);
            if ($recipient) {
                $recipient->update([
                    'status'        => EmailCampaignRecipient::STATUS_FAILED,
                    'error_message' => $reason,
                ]);
                $recipient->campaign?->increment('failed_count');
            }
        }
    }

    /**
     * Sanitize error message to prevent leaking database passwords or credentials.
     */
    protected function sanitizeErrorMessage(string $message): string
    {
        // Strip common sensitive substrings
        $sanitized = preg_replace('/password=[^\s;&]+/i', 'password=***', $message);
        $sanitized = preg_replace('/Bearer\s+[a-zA-Z0-9_\-\.]+/i', 'Bearer ***', $sanitized);
        return substr($sanitized, 0, 500);
    }
}
