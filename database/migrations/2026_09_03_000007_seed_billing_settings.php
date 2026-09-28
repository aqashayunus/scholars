<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed billing configuration defaults into the settings table.
 *
 * Uses the existing Setting::set() pattern (key-value with group + label).
 * These can be overridden by Admin via the Billing → Email Settings page.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            // Billing rules
            ['key' => 'billing.invoice_due_days',        'value' => '7',               'group' => 'billing', 'label' => 'Invoice Due Days (after issue date)'],
            ['key' => 'billing.email_sender_name',       'value' => 'Olympia Scholars', 'group' => 'billing', 'label' => 'Email Sender Name'],

            // Reminder toggles
            ['key' => 'billing.reminder_invoice_issued', 'value' => '1', 'group' => 'billing', 'label' => 'Send Invoice Issued Email'],
            ['key' => 'billing.reminder_due_7_days',     'value' => '1', 'group' => 'billing', 'label' => 'Send 7-Day Before Due Reminder'],
            ['key' => 'billing.reminder_due_3_days',     'value' => '1', 'group' => 'billing', 'label' => 'Send 3-Day Before Due Reminder'],
            ['key' => 'billing.reminder_due_today',      'value' => '1', 'group' => 'billing', 'label' => 'Send Due Today Reminder'],
            ['key' => 'billing.reminder_overdue_1_day',  'value' => '1', 'group' => 'billing', 'label' => 'Send 1-Day Overdue Reminder'],
            ['key' => 'billing.reminder_overdue_3_days', 'value' => '1', 'group' => 'billing', 'label' => 'Send 3-Day Overdue Reminder'],
            ['key' => 'billing.reminder_overdue_7_days', 'value' => '1', 'group' => 'billing', 'label' => 'Send 7-Day Overdue Reminder'],

            // Bank transfer instructions (Admin fills these in)
            ['key' => 'billing.bank_name',           'value' => '', 'group' => 'billing', 'label' => 'Bank Name'],
            ['key' => 'billing.bank_account_number', 'value' => '', 'group' => 'billing', 'label' => 'Bank Account Number'],
            ['key' => 'billing.bank_account_name',   'value' => '', 'group' => 'billing', 'label' => 'Bank Account Name'],
            ['key' => 'billing.payment_instructions','value' => '', 'group' => 'billing', 'label' => 'Additional Payment Instructions'],
        ];

        foreach ($defaults as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value'      => $setting['value'],
                    'group'      => $setting['group'],
                    'label'      => $setting['label'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'billing')
            ->delete();
    }
};
