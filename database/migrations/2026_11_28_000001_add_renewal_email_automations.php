<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The "Continue in {school year}?" invitation and reminder become normal,
 * editable templates + automations in Admin > Email Settings (2026-11-28).
 * EmailTemplateSeeder is not run on deploy, so this migration creates them.
 * Existing rows (e.g. edited by admin) are never overwritten.
 */
return new class extends Migration
{
    private const TEMPLATES = [
        'renewal_continue_invitation' => [
            'name' => 'Continue Next School Year (Invitation)',
            'subject' => 'Continue with Olympia Scholars in {{school_year}}?',
            'body' => "Dear {{recipient_name}},\n\nThank you for learning with Olympia Scholars. Will {{student_name}} continue with us in {{school_year}}?\n\nPlease let us know by {{reply_by_date}}. Open Manage Subscription, confirm {{student_name}}'s Form / Year for {{school_year}} and choose the subjects. The first monthly invoice ({{first_billing_month}}) is issued once you confirm, and then on the 1st of each month.\n\nIf {{student_name}} will not continue, you can tell us on the same page.",
            'cta_label' => 'Manage Subscription',
            'cta_url' => '{{subscription_url}}',
        ],
        'renewal_continue_reminder' => [
            'name' => 'Continue Next School Year (Reminder)',
            'subject' => 'Reminder: continue with Olympia Scholars in {{school_year}}?',
            'body' => "Dear {{recipient_name}},\n\nThis is a friendly reminder: we have not heard yet whether {{student_name}} will continue with Olympia Scholars in {{school_year}}.\n\nPlease let us know by {{reply_by_date}} on the Manage Subscription page. After that date, classes are paused for students who have not confirmed.",
            'cta_label' => 'Manage Subscription',
            'cta_url' => '{{subscription_url}}',
        ],
    ];

    private const AUTOMATIONS = [
        'renewal_invitation' => [
            'name' => 'Continue Next School Year: Invitation',
            'trigger_event' => 'When admin sends "Continue in {school year}?" from Admin > Renewals',
            'description' => 'Asks the student and linked parents to confirm the next school year on Manage Subscription.',
            'template_key' => 'renewal_continue_invitation',
        ],
        'renewal_reminder' => [
            'name' => 'Continue Next School Year: Reminder',
            'trigger_event' => 'When admin sends a reminder from Admin > Renewals',
            'description' => 'Reminds families who have not answered the invitation yet.',
            'template_key' => 'renewal_continue_reminder',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('email_templates') || ! Schema::hasTable('email_automations')) {
            return;
        }

        foreach (self::TEMPLATES as $key => $t) {
            if (DB::table('email_templates')->where('key', $key)->exists()) {
                continue;
            }
            DB::table('email_templates')->insert(array_merge($t, [
                'key' => $key,
                'category' => 'renewal',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        foreach (self::AUTOMATIONS as $key => $a) {
            if (DB::table('email_automations')->where('key', $key)->exists()) {
                continue;
            }
            $templateId = DB::table('email_templates')->where('key', $a['template_key'])->value('id');
            DB::table('email_automations')->insert([
                'key' => $key,
                'name' => $a['name'],
                'category' => 'renewal',
                'trigger_event' => $a['trigger_event'],
                'description' => $a['description'],
                'email_template_id' => $templateId,
                'is_enabled' => true,
                'delay_minutes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('email_automations')->whereIn('key', array_keys(self::AUTOMATIONS))->delete();
        DB::table('email_templates')->whereIn('key', array_keys(self::TEMPLATES))->delete();
    }
};
