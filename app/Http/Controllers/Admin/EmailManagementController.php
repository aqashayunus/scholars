<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailAutomation;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\AudienceResolverService;
use App\Services\Email\EmailAutomationService;
use App\Services\Email\EmailSendingService;
use App\Services\Email\EmailTemplateRenderer;
use App\Services\Email\EmailVariableRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailManagementController extends Controller
{
    /**
     * AJAX: Preview estimated audience count and sample recipients.
     */
    public function previewAudience(Request $request): JsonResponse
    {
        $audienceType = $request->input('audience_type', 'all_parents');
        $filters = $request->input('filters', []);

        $recipients = AudienceResolverService::resolve($audienceType, is_array($filters) ? $filters : []);

        return response()->json([
            'count'  => $recipients->count(),
            'sample' => $recipients->take(8)->map(fn($r) => [
                'name'  => $r['name'] ?? 'N/A',
                'email' => $r['email'],
                'type'  => ucfirst($r['recipient_type']),
            ]),
        ]);
    }

    /**
     * AJAX: Live Preview rendered HTML & plain-text.
     */
    public function previewTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'subject'   => 'nullable|string|max:255',
            'body'      => 'required|string',
            'cta_label' => 'nullable|string|max:100',
            'cta_url'   => 'nullable|string|max:500',
        ]);

        // Sample context data for preview
        $sampleContext = [
            'parent_name'        => 'Puan Siti Aminah binti Razali',
            'parent_email'       => 'siti.aminah@example.com',
            'parent_phone'       => '+60 12-345 6789',
            'student_name'       => 'Muhammad Danish bin Razak',
            'student_id'         => 'STU-00142',
            'student_email'      => 'danish.razak@example.com',
            'programme_name'     => 'Olympia Secondary Core Programme (Form 3)',
            'subject_name'       => 'Mathematics (Form 3)',
            'class_name'         => 'Form 3 Advanced Mathematics — Cohort Alpha',
            'teacher_name'       => 'Ustaz Daniel bin Ahmad',
            'invoice_number'     => 'INV-2026-00389',
            'invoice_amount'     => 'RM 350.00',
            'amount_paid'        => 'RM 350.00',
            'outstanding_amount' => 'RM 0.00',
            'due_date'           => now()->addDays(7)->format('d M Y'),
            'payment_date'       => now()->format('d M Y, h:i A'),
            'payment_reference'  => 'FPX-MY-20261005-9921',
            'payment_status'     => 'PAID',
            'receipt_number'     => 'REC-2026-00214',
            'class_date'         => now()->addDay()->format('d M Y'),
            'class_time'         => '08:00 PM',
            'class_schedule'     => now()->addDay()->format('d M Y, 08:00 PM (60 mins)'),
            'meeting_url'        => url('/student/dashboard#timetable'),
            'class_status'       => 'UPCOMING',
            'assessment_name'    => 'Diagnostic Test 1: Algebraic Expressions',
            'score'              => '92% (Grade A)',
        ];

        $rendered = EmailTemplateRenderer::render(
            $request->body,
            $request->subject ?: 'Olympia Scholars Official Preview',
            $sampleContext,
            null,
            $request->cta_label,
            $request->cta_url
        );

        return response()->json([
            'subject'    => $rendered['subject'],
            'html'       => $rendered['html'],
            'plain_text' => $rendered['plain_text'],
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|in:' . implode(',', array_keys(EmailTemplate::categories())),
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'cta_label'     => 'nullable|string|max:100',
            'cta_url'       => 'nullable|string|max:500',
            'automation_id' => 'nullable|exists:email_automations,id',
        ]);

        $key = Str::slug($request->category . ' ' . $request->name, '_');
        $uniqueKey = $key;
        $counter = 1;
        while (EmailTemplate::where('key', $uniqueKey)->exists()) {
            $uniqueKey = $key . '_' . $counter++;
        }

        $template = EmailTemplate::create([
            'name'       => $request->name,
            'key'        => $uniqueKey,
            'category'   => $request->category,
            'subject'    => $request->subject,
            'body'       => $request->body,
            'cta_label'  => $request->cta_label,
            'cta_url'    => $request->cta_url,
            'status'     => EmailTemplate::STATUS_ACTIVE,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Record initial version
        $template->recordVersion(auth()->id());

        // Link to automation if selected
        if ($request->filled('automation_id')) {
            EmailAutomation::where('id', $request->automation_id)->update(['email_template_id' => $template->id]);
        }

        return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
            ->with('success', "Email template '{$template->name}' created successfully.");
    }

    /**
     * Update an existing template.
     */
    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|in:' . implode(',', array_keys(EmailTemplate::categories())),
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'cta_label'     => 'nullable|string|max:100',
            'cta_url'       => 'nullable|string|max:500',
            'automation_id' => 'nullable|exists:email_automations,id',
        ]);

        // Save snapshot to version history
        $template->recordVersion(auth()->id());

        $template->update([
            'name'       => $request->name,
            'category'   => $request->category,
            'subject'    => $request->subject,
            'body'       => $request->body,
            'cta_label'  => $request->cta_label,
            'cta_url'    => $request->cta_url,
            'updated_by' => auth()->id(),
        ]);

        // Link or update automation if provided
        if ($request->filled('automation_id')) {
            EmailAutomation::where('id', $request->automation_id)->update(['email_template_id' => $template->id]);
        }

        return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
            ->with('success', "Template '{$template->name}' updated successfully (Version saved).");
    }

    /**
     * Duplicate a template.
     */
    public function duplicateTemplate(EmailTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->key  = $template->key . '_copy_' . Str::random(4);
        $newTemplate->status = EmailTemplate::STATUS_ACTIVE;
        $newTemplate->created_by = auth()->id();
        $newTemplate->updated_by = auth()->id();
        $newTemplate->save();

        $newTemplate->recordVersion(auth()->id());

        return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
            ->with('success', "Template duplicated as '{$newTemplate->name}'.");
    }

    /**
     * Toggle active/inactive template status.
     */
    public function toggleTemplate(EmailTemplate $template)
    {
        $newStatus = $template->status === EmailTemplate::STATUS_ACTIVE
            ? EmailTemplate::STATUS_INACTIVE
            : EmailTemplate::STATUS_ACTIVE;

        $template->update(['status' => $newStatus, 'updated_by' => auth()->id()]);

        return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
            ->with('success', "Template status changed to " . ucfirst($newStatus) . ".");
    }

    /**
     * Archive template.
     */
    public function archiveTemplate(EmailTemplate $template)
    {
        $template->update(['status' => EmailTemplate::STATUS_ARCHIVED, 'updated_by' => auth()->id()]);

        return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
            ->with('success', "Template '{$template->name}' archived.");
    }

    /**
     * Send a safe test email to an administrator.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'cta_label'  => 'nullable|string|max:100',
            'cta_url'    => 'nullable|string|max:500',
        ]);

        $recipientEmail = strtolower(trim($request->test_email));

        $context = [
            'recipient_name'  => auth()->user()->name ?? 'Administrator',
            'recipient_email' => $recipientEmail,
            'parent_name'     => 'Puan Siti Aminah (Sample)',
            'student_name'    => 'Muhammad Danish (Sample)',
            'student_id'      => 'STU-00142',
            'programme_name'  => 'Olympia Secondary Core Programme',
            'subject_name'    => 'Mathematics (Form 3)',
            'class_name'      => 'Form 3 Advanced Mathematics',
            'teacher_name'    => 'Ustaz Daniel bin Ahmad',
            'invoice_number'  => 'INV-2026-TEST',
            'invoice_amount'  => 'RM 350.00',
            'amount_paid'     => 'RM 350.00',
            'outstanding_amount' => 'RM 0.00',
            'due_date'        => now()->addDays(7)->format('d M Y'),
            'payment_date'    => now()->format('d M Y, h:i A'),
            'payment_status'  => 'PAID',
            'receipt_number'  => 'REC-2026-TEST',
            'class_date'      => now()->addDay()->format('d M Y'),
            'class_time'      => '08:00 PM',
            'meeting_url'     => url('/student/dashboard#timetable'),
            'score'           => '95%',
        ];

        try {
            $log = EmailSendingService::send(
                $recipientEmail,
                '[TEST] ' . $request->subject,
                $request->body,
                $context,
                'test',
                auth()->id(),
                null,
                null,
                null,
                null,
                true // sync
            );

            if ($log && $log->status === EmailLog::STATUS_FAILED) {
                return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
                    ->with('error', "Test email failed: " . ($log->error_message ?? 'Mail delivery failed. Please check your SMTP mail settings.'));
            }

            return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
                ->with('success', "Test email sent successfully to {$recipientEmail}. Check your inbox or mail log.");
        } catch (\Throwable $e) {
            return redirect()->to(route('admin.dashboard') . '#emailsettings-templates')
                ->with('error', "Could not send test email: " . $e->getMessage());
        }
    }

    /**
     * Create and dispatch / schedule a bulk email blast campaign.
     */
    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'category'         => 'required|string',
            'description'      => 'nullable|string|max:1000',
            'subject'          => 'required|string|max:255',
            'body'             => 'required|string',
            'cta_label'        => 'nullable|string|max:100',
            'cta_url'          => 'nullable|string|max:500',
            'template_id'      => 'nullable|exists:email_templates,id',
            'audience_type'    => 'required|string|in:' . implode(',', array_keys(AudienceResolverService::supportedAudiences())),
            'delivery_type'    => 'required|in:immediate,scheduled,draft',
            'scheduled_at'     => 'nullable|required_if:delivery_type,scheduled|date|after:now',
            'programme_filter' => 'nullable|string',
            'subject_filter'   => 'nullable|exists:subjects,id',
            'class_filter'     => 'nullable|exists:live_classes,id',
        ]);

        $filters = [];
        if ($request->filled('programme_filter')) $filters['programme'] = $request->programme_filter;
        if ($request->filled('subject_filter'))   $filters['subject_id'] = $request->subject_filter;
        if ($request->filled('class_filter'))     $filters['class_id'] = $request->class_filter;

        // Resolve recipients
        $recipients = AudienceResolverService::resolve($request->audience_type, $filters);

        if ($request->delivery_type !== 'draft' && $recipients->isEmpty()) {
            return back()->with('error', "Cannot launch campaign: no active, non-suppressed recipients matched the selected audience.");
        }

        $status = match ($request->delivery_type) {
            'immediate' => EmailCampaign::STATUS_QUEUED,
            'scheduled' => EmailCampaign::STATUS_SCHEDULED,
            default     => EmailCampaign::STATUS_DRAFT,
        };

        $scheduledAt = $request->delivery_type === 'scheduled' ? $request->scheduled_at : null;

        $campaign = EmailCampaign::create([
            'name'             => $request->name,
            'category'         => $request->category,
            'description'      => $request->description,
            'subject'          => $request->subject,
            'body'             => $request->body,
            'cta_label'        => $request->cta_label,
            'cta_url'          => $request->cta_url,
            'template_id'      => $request->template_id ?: null,
            'audience_type'    => $request->audience_type,
            'audience_filters' => $filters,
            'total_recipients' => $recipients->count(),
            'sent_count'       => 0,
            'failed_count'     => 0,
            'status'           => $status,
            'scheduled_at'     => $scheduledAt,
            'created_by'       => auth()->id(),
        ]);

        // Populate campaign recipients
        foreach ($recipients as $r) {
            EmailCampaignRecipient::create([
                'email_campaign_id'  => $campaign->id,
                'user_id'            => $r['user_id'],
                'email'              => $r['email'],
                'name'               => $r['name'],
                'recipient_type'     => $r['recipient_type'],
                'related_student_id' => $r['related_student_id'],
                'status'             => EmailCampaignRecipient::STATUS_PENDING,
            ]);
        }

        // If immediate delivery, dispatch processing job right away
        if ($campaign->status === EmailCampaign::STATUS_QUEUED) {
            EmailSendingService::dispatchCampaign($campaign);
            $msg = "Campaign '{$campaign->name}' queued for sending to {$recipients->count()} recipient(s).";
        } elseif ($campaign->status === EmailCampaign::STATUS_SCHEDULED) {
            $msg = "Campaign '{$campaign->name}' scheduled to send at " . Carbon::parse($scheduledAt)->format('d M Y, h:i A') . " to {$recipients->count()} recipient(s).";
        } else {
            $msg = "Campaign '{$campaign->name}' saved as draft with {$recipients->count()} recipient(s).";
        }

        return redirect()->to(route('admin.dashboard') . '#emailsettings-campaigns')
            ->with('success', $msg);
    }

    /**
     * Cancel a scheduled or queued campaign.
     */
    public function cancelCampaign(EmailCampaign $campaign)
    {
        if (!$campaign->isCancellable()) {
            return back()->with('error', "Campaign cannot be cancelled in its current state ({$campaign->status}).");
        }

        $campaign->update(['status' => EmailCampaign::STATUS_CANCELLED]);

        return redirect()->to(route('admin.dashboard') . '#emailsettings-campaigns')
            ->with('success', "Campaign '{$campaign->name}' has been cancelled.");
    }

    /**
     * Toggle automation on/off.
     */
    public function toggleAutomation(EmailAutomation $automation)
    {
        $automation->update(['is_enabled' => !$automation->is_enabled]);

        $statusStr = $automation->is_enabled ? 'enabled' : 'disabled';
        return redirect()->to(route('admin.dashboard') . '#emailsettings-automations')
            ->with('success', "Automation '{$automation->name}' is now {$statusStr}.");
    }

    /**
     * Update automation configuration (template & delay).
     */
    public function updateAutomation(Request $request, EmailAutomation $automation)
    {
        $request->validate([
            'email_template_id' => 'required|exists:email_templates,id',
            'delay_minutes'     => 'nullable|integer|min:0|max:10080',
        ]);

        $automation->update([
            'email_template_id' => $request->email_template_id,
            'delay_minutes'     => (int) ($request->delay_minutes ?? 0),
        ]);

        return redirect()->to(route('admin.dashboard') . '#emailsettings-automations')
            ->with('success', "Automation '{$automation->name}' updated.");
    }

    /**
     * Safely test-fire an automation using mock payload.
     */
    public function testAutomation(Request $request, EmailAutomation $automation)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $adminEmail = strtolower(trim($request->test_email));

        $testContext = [
            'recipient_name'  => auth()->user()->name ?? 'Administrator',
            'recipient_email' => $adminEmail,
            'parent_name'     => auth()->user()->name ?? 'Administrator',
            'student_name'    => 'Muhammad Danish (Test)',
            'student_id'      => 'STU-00142',
            'programme_name'  => 'Olympia Scholars Core Package',
            'subject_name'    => 'Mathematics',
            'class_name'      => 'Form 3 Mathematics Alpha',
            'teacher_name'    => 'Ustaz Daniel',
            'invoice_number'  => 'INV-TEST-001',
            'invoice_amount'  => 'RM 350.00',
            'amount_paid'     => 'RM 350.00',
            'outstanding_amount' => 'RM 0.00',
            'due_date'        => now()->addDays(7)->format('d M Y'),
            'payment_date'    => now()->format('d M Y, h:i A'),
            'class_date'      => now()->addDay()->format('d M Y'),
            'class_time'      => '08:00 PM',
            'meeting_url'     => url('/student/dashboard#timetable'),
            'score'           => '95%',
        ];

        $res = EmailAutomationService::trigger($automation->key, $testContext, 'test_' . Str::random(8), sync: true, isTest: true);

        $isSuccess = is_array($res) ? ($res['success'] ?? false) : (bool) $res;

        if ($isSuccess) {
            return redirect()->to(route('admin.dashboard') . '#emailsettings-automations')
                ->with('success', "Automation test triggered! An email has been dispatched to {$adminEmail}.");
        } else {
            $errorMsg = is_array($res) && !empty($res['error'])
                ? $res['error']
                : 'Could not trigger automation. Ensure template is active and mail server is reachable.';
            return redirect()->to(route('admin.dashboard') . '#emailsettings-automations')
                ->with('error', "Automation test failed: {$errorMsg}");
        }
    }

    /**
     * Retry an eligible failed email log.
     */
    public function retryLog(EmailLog $log)
    {
        $success = EmailSendingService::retryFailed($log);

        if ($success) {
            return back()->with('success', "Email #{$log->id} re-queued for delivery.");
        } else {
            return back()->with('error', "Could not retry email #{$log->id}: " . ($log->error_message ?? 'Status is not failed'));
        }
    }

    /**
     * Export email audit logs to CSV.
     */
    public function exportLogsCsv(Request $request): StreamedResponse
    {
        $query = EmailLog::latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient_email', 'like', "%{$s}%")
                  ->orWhere('recipient_name', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%");
            });
        }

        $logs = $query->take(2000)->get();

        $fileName = 'olympia_email_logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Recipient Name', 'Recipient Email', 'Recipient Type', 'Category', 'Subject', 'Status', 'Attempts', 'Error Message']);

            foreach ($logs as $l) {
                fputcsv($handle, [
                    'LOG-' . str_pad($l->id, 6, '0', STR_PAD_LEFT),
                    $l->created_at ? $l->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $l->recipient_name ?? 'N/A',
                    $l->recipient_email,
                    ucfirst($l->recipient_type ?? 'N/A'),
                    ucfirst($l->category ?? 'General'),
                    $l->subject,
                    strtoupper($l->status),
                    $l->attempts,
                    $l->error_message ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Update global institutional email settings & banner upload.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'sender_display_name'    => 'required|string|max:100',
            'sender_email_address'   => 'required|email|max:150',
            'reply_to_email'         => 'nullable|email|max:150',
            'official_support_email' => 'required|email|max:150',
            'official_website_url'   => 'required|url|max:255',
            'official_email_footer'  => 'required|string|max:500',
            'email_banner_url'       => 'nullable|url|max:500',
            'banner_image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072', // max 3MB
        ]);

        Setting::set('sender_display_name', $request->sender_display_name, 'email', 'Official Email Sender Name');
        Setting::set('sender_email_address', $request->sender_email_address, 'email', 'Official Outgoing Email Address');
        Setting::set('reply_to_email', $request->reply_to_email ?? $request->sender_email_address, 'email', 'Reply-To Email Address');
        Setting::set('official_support_email', $request->official_support_email, 'email', 'Official Support Email');
        Setting::set('official_website_url', $request->official_website_url, 'email', 'Official Website URL');
        Setting::set('official_email_footer', $request->official_email_footer, 'email', 'Official Email Signature & Footer');

        // Handle direct Banner URL input
        if ($request->filled('email_banner_url')) {
            Setting::set('email_banner_url', trim($request->email_banner_url), 'email', 'Custom Email Banner URL');
        }

        // Handle Banner image upload (takes priority if new file uploaded)
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $filename = 'email-banner-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/banners', $filename);
            // Publicly accessible storage link
            $publicUrl = asset('storage/banners/' . $filename);
            Setting::set('email_banner_url', $publicUrl, 'email', 'Custom Email Banner URL');
        }

        return redirect()->to(route('admin.dashboard') . '#emailsettings-settings')
            ->with('success', 'Official email settings and institutional branding updated successfully.');
    }
}
