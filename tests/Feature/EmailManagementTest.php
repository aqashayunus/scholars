<?php

namespace Tests\Feature;

use App\Models\EmailAutomation;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\Email\AudienceResolverService;
use App\Services\Email\EmailAutomationService;
use App\Services\Email\EmailSendingService;
use App\Services\Email\EmailTemplateRenderer;
use App\Services\Email\EmailVariableRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $parentUser;
    private User $studentUser;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin
        $this->admin = User::create([
            'name'     => 'System Admin',
            'email'    => 'admin@olympianscholars.my',
            'role'     => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // 2. Create Parent
        $this->parentUser = User::create([
            'name'     => 'Parent Tan',
            'email'    => 'parent.tan@example.com',
            'role'     => 'parent',
            'password' => Hash::make('password123'),
        ]);

        // 3. Create Student
        $this->studentUser = User::create([
            'name'     => 'Student Tan Wei',
            'email'    => 'wei@olympianscholars.my',
            'role'     => 'student',
            'password' => Hash::make('password123'),
        ]);

        $this->student = Student::create([
            'user_id'        => $this->studentUser->id,
            'parent_user_id' => $this->parentUser->id,
            'level'          => 'SPM',
            'status'         => 'active',
        ]);
    }

    /**
     * Test 1: Admin can access dashboard with #emailsettings and email data is loaded
     */
    public function test_admin_can_access_email_management_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('tab-emailsettings');
        $response->assertSee('Email Settings');
        $response->assertSee('Email Templates');
        $response->assertSee('Email Blast / Campaigns');
    }

    /**
     * Test 2: Non-admin is restricted from email management endpoints
     */
    public function test_non_admin_cannot_access_email_management_endpoints(): void
    {
        $postResponse = $this->actingAs($this->studentUser)->post(route('admin.email.templates.store'), [
            'name'        => 'Hacked Template',
            'category'    => 'general',
            'subject'     => 'Subject',
            'body_text'   => 'Body',
        ]);
        $postResponse->assertStatus(403);
    }

    /**
     * Test 3: Template CRUD (Create, Update, Duplicate, Toggle Status)
     */
    public function test_admin_can_manage_templates(): void
    {
        // 1. Create Template
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.email.templates.store'), [
            'name'        => 'Test Formal Notice',
            'category'    => 'general',
            'subject'     => 'Official Notice for {{student_name}}',
            'body'        => "Dear {{parent_name}},\n\nThis is an official notice regarding {{student_name}}.\n\nRegards,\nOlympia Admin",
            'cta_label'   => 'View Portal',
            'cta_url'     => 'https://portal.olympianscholars.my',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('email_templates', [
            'name'     => 'Test Formal Notice',
            'category' => 'general',
        ]);

        $template = EmailTemplate::where('name', 'Test Formal Notice')->first();
        $this->assertNotNull($template);

        // 2. Update Template
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.email.templates.update', $template), [
            'name'        => 'Updated Formal Notice',
            'category'    => 'general',
            'subject'     => 'Updated Notice for {{student_name}}',
            'body'        => "Dear {{parent_name}},\n\nUpdated text.\n\nRegards,\nOlympia Admin",
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('email_templates', [
            'id'   => $template->id,
            'name' => 'Updated Formal Notice',
        ]);

        // 3. Duplicate Template
        $dupResponse = $this->actingAs($this->admin)->post(route('admin.email.templates.duplicate', $template));
        $dupResponse->assertRedirect();
        $this->assertDatabaseHas('email_templates', [
            'name' => 'Updated Formal Notice (Copy)',
        ]);

        // 4. Toggle Status
        $toggleResponse = $this->actingAs($this->admin)->post(route('admin.email.templates.toggle', $template));
        $toggleResponse->assertRedirect();
        $this->assertEquals(EmailTemplate::STATUS_INACTIVE, $template->fresh()->status);
    }

    /**
     * Test 4: Variable substitution and Safe HTML escaping
     */
    public function test_variable_registry_and_template_renderer_escape_html_safely(): void
    {
        $context = [
            'parent' => $this->parentUser,
            'student' => $this->student,
            'user' => $this->parentUser,
            'custom_xss' => '<script>alert("XSS")</script>',
        ];

        $renderedVariables = EmailVariableRegistry::renderAll($context);

        $this->assertEquals(strtoupper($this->parentUser->name), $renderedVariables['parent_name']);
        $this->assertEquals(strtoupper($this->studentUser->name), $renderedVariables['student_name']);

        // Check template renderer
        $body = "Dear {{parent_name}},\n\nHello from Olympia.\nMalicious input: {{custom_xss}}";
        $html = EmailTemplateRenderer::renderHtml($body, $renderedVariables);

        $this->assertStringContainsString('Dear ' . strtoupper($this->parentUser->name) . ',', $html);
        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $html);
    }

    /**
     * Test 5: Audience Resolution & Suppression filtering
     */
    public function test_audience_resolver_resolves_recipients_and_respects_suppressions(): void
    {
        // Add a teacher
        User::create([
            'name'     => 'Teacher Lee',
            'email'    => 'lee@olympianscholars.my',
            'role'     => 'teacher',
            'password' => Hash::make('password123'),
        ]);

        // Suppress Parent Tan
        EmailSuppression::create([
            'email'  => 'parent.tan@example.com',
            'reason' => 'unsubscribe',
        ]);

        // Parents audience should exclude suppressed parent
        $parentAudience = AudienceResolverService::resolve('all_parents');
        $parentEmails = $parentAudience->pluck('email')->all();
        $this->assertNotContains('parent.tan@example.com', $parentEmails);

        // Teachers audience should include teacher
        $teacherAudience = AudienceResolverService::resolve('all_teachers');
        $teacherEmails = $teacherAudience->pluck('email')->all();
        $this->assertContains('lee@olympianscholars.my', $teacherEmails);
    }

    /**
     * Test 6: Campaign creation, queued batching, and cancellation
     */
    public function test_admin_can_create_and_cancel_campaign(): void
    {
        Queue::fake();

        $template = EmailTemplate::create([
            'name'      => 'Holiday Announcement',
            'key'       => 'holiday_announce_' . uniqid(),
            'category'  => 'general',
            'subject'   => 'Upcoming Term Break Notice',
            'body'      => 'Term break starts next week.',
            'status'    => EmailTemplate::STATUS_ACTIVE,
        ]);

        // 1. Create campaign
        $response = $this->actingAs($this->admin)->post(route('admin.email.campaigns.store'), [
            'name'          => 'Term Break 2026',
            'category'      => 'general',
            'audience_type' => 'all_parents',
            'template_id'   => $template->id,
            'subject'       => 'Upcoming Term Break Notice',
            'body'          => 'Term break starts next week.',
            'delivery_type' => 'scheduled',
            'scheduled_at'  => now()->addDays(2)->format('Y-m-d H:i'),
        ]);

        $response->assertRedirect();
        $campaign = EmailCampaign::where('name', 'Term Break 2026')->first();
        $this->assertNotNull($campaign);
        $this->assertEquals(EmailCampaign::STATUS_SCHEDULED, $campaign->status);

        // 2. Cancel campaign
        $cancelResponse = $this->actingAs($this->admin)->post(route('admin.email.campaigns.cancel', $campaign));
        $cancelResponse->assertRedirect();
        $this->assertEquals(EmailCampaign::STATUS_CANCELLED, $campaign->fresh()->status);
    }

    /**
     * Test 7: Automation event triggering with idempotency protection
     */
    public function test_email_automation_triggers_safely_with_idempotency(): void
    {
        Mail::fake();

        $template = EmailTemplate::create([
            'name'      => 'Auto Invoice Issued',
            'key'       => 'invoice_issued',
            'category'  => 'payment',
            'subject'   => 'New Invoice #{{invoice_number}} Issued',
            'body'      => 'Dear {{parent_name}}, Invoice #{{invoice_number}} for RM {{invoice_amount}} has been issued.',
            'status'    => EmailTemplate::STATUS_ACTIVE,
        ]);

        $automation = EmailAutomation::create([
            'key'               => 'invoice_issued',
            'trigger_event'     => 'invoice_issued',
            'name'              => 'Invoice Issued Notice',
            'category'          => 'payment',
            'email_template_id' => $template->id,
            'is_enabled'        => true,
            'recipient_type'    => 'parent',
        ]);

        $invoice = Invoice::create([
            'student_id'     => $this->student->id,
            'invoice_number' => 'INV-2026-TEST-001',
            'title'          => 'Term Fee',
            'subtotal'       => 150.00,
            'total_amount'   => 150.00,
            'amount_paid'    => 0.00,
            'due_date'       => now()->addDays(14),
            'status'         => Invoice::STATUS_ISSUED,
        ]);

        // First trigger: should execute
        $idempotencyKey = "invoice_{$invoice->id}_issued_test";
        $executedFirst = EmailAutomationService::trigger(
            'invoice_issued',
            [
                'invoice' => $invoice,
                'student' => $this->student,
                'parent'  => $this->parentUser,
                'user'    => $this->parentUser,
            ],
            $idempotencyKey
        );

        $this->assertTrue($executedFirst);
        $this->assertDatabaseHas('email_automation_executions', [
            'idempotency_key' => $idempotencyKey,
            'status'          => 'success',
        ]);

        // Second trigger with SAME idempotency key: must be skipped!
        $executedSecond = EmailAutomationService::trigger(
            'invoice_issued',
            [
                'invoice' => $invoice,
                'student' => $this->student,
            ],
            $idempotencyKey
        );

        $this->assertFalse($executedSecond);

        // Automation execution count should still be 1
        $this->assertEquals(1, $automation->fresh()->success_count);
    }

    /**
     * Test 8: Email logging, Retry, and CSV export
     */
    public function test_email_log_and_retry_workflow(): void
    {
        Mail::fake();

        // Create a failed log
        $log = EmailLog::create([
            'recipient_email' => 'retry.test@example.com',
            'recipient_type'  => 'parent',
            'subject'         => 'Test Subject for Retry',
            'category'        => 'general',
            'body_preview'    => 'Test body content.',
            'status'          => EmailLog::STATUS_FAILED,
            'error_message'   => 'Connection timed out',
            'attempts'        => 1,
        ]);

        // Retry the log
        $response = $this->actingAs($this->admin)->post(route('admin.email.logs.retry', $log));
        $response->assertRedirect();

        $this->assertTrue(in_array($log->fresh()->status, [EmailLog::STATUS_QUEUED, EmailLog::STATUS_SENT]));

        // Test CSV Export
        $exportResponse = $this->actingAs($this->admin)->get(route('admin.email.logs.export-csv'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
    }

    /**
     * Test 9: Settings update and banner storage validation
     */
    public function test_admin_can_update_email_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.email.settings.update'), [
            'sender_display_name'    => 'Olympia Scholars Official',
            'sender_email_address'   => 'notifications@olympianscholars.my',
            'reply_to_email'         => 'support@olympianscholars.my',
            'official_support_email' => 'helpdesk@olympianscholars.my',
            'official_email_footer'  => 'Official Institutional Communication — Olympia Education Malaysia',
            'official_website_url'   => 'https://olympianscholars.my',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key'   => 'sender_display_name',
            'value' => 'Olympia Scholars Official',
        ]);
        $this->assertDatabaseHas('settings', [
            'key'   => 'sender_email_address',
            'value' => 'notifications@olympianscholars.my',
        ]);
    }

    /**
     * Test 10: Admin can test-fire an automation safely
     */
    public function test_admin_can_test_fire_automation(): void
    {
        Mail::fake();

        $tpl = EmailTemplate::firstOrCreate(
            ['key' => 'test_fire_tpl'],
            [
                'name'     => 'Test Fire Template',
                'category' => EmailTemplate::CATEGORY_GREETINGS,
                'subject'  => 'Test Fire Subject',
                'body'     => 'Hello {{recipient_name}}',
                'status'   => EmailTemplate::STATUS_ACTIVE,
            ]
        );

        $automation = EmailAutomation::firstOrCreate(
            ['key' => 'test_fire_auto'],
            [
                'name'              => 'Test Fire Auto',
                'category'          => EmailTemplate::CATEGORY_GREETINGS,
                'trigger_event'     => 'Testing',
                'email_template_id' => $tpl->id,
                'is_enabled'        => true,
            ]
        );

        $response = $this->actingAs($this->admin)->post(
            route('admin.email.automations.test', $automation),
            ['test_email' => 'admin_test@olympiascholars.my']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
