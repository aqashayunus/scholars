<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Services\Email\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;
    public int $batchSize;

    /**
     * Create a new job instance.
     */
    public function __construct(int $campaignId, int $batchSize = 50)
    {
        $this->campaignId = $campaignId;
        $this->batchSize  = $batchSize;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = EmailCampaign::with(['template'])->find($this->campaignId);

        if (!$campaign) {
            return;
        }

        // Guard: do not process cancelled campaigns
        if ($campaign->status === EmailCampaign::STATUS_CANCELLED) {
            Log::info("[ProcessCampaignBatchJob] Campaign #{$this->campaignId} was cancelled. Skipping.");
            return;
        }

        // Mark as processing on initial pick up
        if ($campaign->status !== EmailCampaign::STATUS_PROCESSING) {
            $campaign->update([
                'status'     => EmailCampaign::STATUS_PROCESSING,
                'started_at' => $campaign->started_at ?? now(),
            ]);
        }

        // Fetch a chunk of pending recipients
        $recipients = EmailCampaignRecipient::where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->with(['user', 'student.user'])
            ->limit($this->batchSize)
            ->get();

        if ($recipients->isEmpty()) {
            // All recipients processed! Complete the campaign
            $campaign->update([
                'status'       => EmailCampaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
            Log::info("[ProcessCampaignBatchJob] Campaign #{$this->campaignId} '{$campaign->name}' completed successfully.");
            return;
        }

        foreach ($recipients as $recipient) {
            // Re-check cancellation state between chunks
            if ($campaign->fresh()->status === EmailCampaign::STATUS_CANCELLED) {
                break;
            }

            // Prepare context for variable substitution
            $context = [
                'recipient_name'  => $recipient->name,
                'recipient_email' => $recipient->email,
            ];

            if ($recipient->user) {
                $context['user'] = $recipient->user;
            }
            if ($recipient->student) {
                $context['student'] = $recipient->student;
            }

            // Render message
            $rendered = EmailTemplateRenderer::render(
                $campaign->body,
                $campaign->subject,
                $context,
                null,
                $campaign->cta_label,
                $campaign->cta_url
            );

            // Create initial pending log
            $log = EmailLog::create([
                'email_campaign_id'   => $campaign->id,
                'email_template_id'   => $campaign->template_id,
                'recipient_user_id'   => $recipient->user_id,
                'recipient_email'     => $recipient->email,
                'recipient_name'      => $recipient->name,
                'recipient_type'      => $recipient->recipient_type,
                'related_student_id'  => $recipient->related_student_id,
                'subject'             => $rendered['subject'],
                'category'            => $campaign->category ?? 'general',
                'body_preview'        => substr(strip_tags($rendered['plain_text']), 0, 250),
                'status'              => EmailLog::STATUS_QUEUED,
                'queued_at'           => now(),
            ]);

            // Dispatch individual email job
            SendEmailMailableJob::dispatch(
                $recipient->email,
                $rendered['subject'],
                $rendered['html'],
                $rendered['plain_text'],
                $log->id,
                $recipient->id
            );
        }

        // Check if there are still more pending recipients
        $hasMore = EmailCampaignRecipient::where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->exists();

        if ($hasMore) {
            // Dispatch next batch
            self::dispatch($campaign->id, $this->batchSize)->delay(now()->addSeconds(2));
        } else {
            $campaign->update([
                'status'       => EmailCampaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }
}
