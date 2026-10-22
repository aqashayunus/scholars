<?php

namespace App\Services\Email;

use App\Jobs\ProcessCampaignBatchJob;
use App\Jobs\SendEmailMailableJob;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSendingService
{
    /**
     * Send an individual email (synchronous or queued) with full logging.
     */
    public static function send(
        string $toEmail,
        string $subject,
        EmailTemplate|string $templateOrBody,
        array $context = [],
        string $category = 'general',
        ?int $userId = null,
        ?int $studentId = null,
        ?int $invoiceId = null,
        ?int $templateId = null,
        ?int $automationId = null,
        bool $sync = false
    ): EmailLog {
        $toEmail = strtolower(trim($toEmail));

        // 1. Check suppression list
        $isSuppressed = EmailSuppression::isSuppressed($toEmail);

        // 2. Render template with variables
        $rendered = EmailTemplateRenderer::render($templateOrBody, $subject, $context);

        // 3. Create initial EmailLog record
        $emailLog = EmailLog::create([
            'email_template_id'   => $templateId ?: ($templateOrBody instanceof EmailTemplate ? $templateOrBody->id : null),
            'email_automation_id' => $automationId,
            'recipient_user_id'   => $userId,
            'recipient_email'     => $toEmail,
            'recipient_name'      => $context['recipient_name'] ?? ($context['user']?->name ?? ($context['student']?->user?->name ?? null)),
            'recipient_type'      => $context['recipient_type'] ?? 'custom',
            'related_student_id'  => $studentId,
            'related_invoice_id'  => $invoiceId,
            'subject'             => $rendered['subject'],
            'category'            => $category,
            'body_preview'        => substr(strip_tags($rendered['plain_text']), 0, 250),
            'status'              => $isSuppressed ? EmailLog::STATUS_FAILED : EmailLog::STATUS_QUEUED,
            'error_message'       => $isSuppressed ? 'Email address is on suppression list.' : null,
            'queued_at'           => now(),
        ]);

        if ($isSuppressed) {
            return $emailLog;
        }

        if ($sync) {
            // Direct synchronous send (e.g. for test email)
            try {
                $mailable = new \App\Mail\OfficialBrandedMail(
                    $rendered['subject'],
                    $rendered['html'],
                    $rendered['plain_text']
                );
                Mail::to($toEmail)->send($mailable);

                $emailLog->update([
                    'status'       => EmailLog::STATUS_SENT,
                    'sent_at'      => now(),
                    'delivered_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error("[EmailSendingService] Sync send failed to {$toEmail}: " . $e->getMessage());
                $emailLog->update([
                    'status'        => EmailLog::STATUS_FAILED,
                    'error_message' => substr($e->getMessage(), 0, 500),
                ]);
            }
        } else {
            // In local development or sync queue driver, dispatch synchronously to ensure delivery
            if (config('queue.default') === 'sync' || app()->environment('local')) {
                try {
                    SendEmailMailableJob::dispatchSync(
                        $toEmail,
                        $rendered['subject'],
                        $rendered['html'],
                        $rendered['plain_text'],
                        $emailLog->id
                    );
                } catch (\Throwable $e) {
                    Log::error("[EmailSendingService] Sync dispatch failed to {$toEmail}: " . $e->getMessage());
                    $emailLog->update([
                        'status'        => EmailLog::STATUS_FAILED,
                        'error_message' => substr($e->getMessage(), 0, 500),
                    ]);
                }
            } else {
                // Asynchronous queued send via SendEmailMailableJob
                SendEmailMailableJob::dispatch(
                    $toEmail,
                    $rendered['subject'],
                    $rendered['html'],
                    $rendered['plain_text'],
                    $emailLog->id
                );
            }
        }

        return $emailLog;
    }

    /**
     * Dispatch an entire campaign for execution.
     */
    public static function dispatchCampaign(EmailCampaign $campaign): void
    {
        // Guard: only dispatch draft or scheduled campaigns
        if (in_array($campaign->status, [EmailCampaign::STATUS_PROCESSING, EmailCampaign::STATUS_COMPLETED])) {
            return;
        }

        $campaign->update([
            'status'     => EmailCampaign::STATUS_QUEUED,
            'started_at' => now(),
        ]);

        // Kick off batch processing job
        ProcessCampaignBatchJob::dispatch($campaign->id, 50);
    }

    /**
     * Retry an eligible failed email log.
     */
    public static function retryFailed(EmailLog $log): bool
    {
        if ($log->status !== EmailLog::STATUS_FAILED) {
            return false;
        }

        if (EmailSuppression::isSuppressed($log->recipient_email)) {
            $log->update(['error_message' => 'Cannot retry: recipient email is currently suppressed.']);
            return false;
        }

        $log->update([
            'status'        => EmailLog::STATUS_QUEUED,
            'error_message' => null,
            'queued_at'     => now(),
        ]);

        // Re-render from template if available, or use logged subject
        $html = view('emails.official-template', [
            'subject'   => $log->subject,
            'htmlBody'  => '<p>' . nl2br(e($log->body_preview)) . '</p>',
            'bannerUrl' => null,
            'ctaLabel'  => null,
            'ctaUrl'    => null,
        ])->render();

        SendEmailMailableJob::dispatch(
            $log->recipient_email,
            $log->subject,
            $html,
            $log->body_preview ?? $log->subject,
            $log->id
        );

        return true;
    }
}
