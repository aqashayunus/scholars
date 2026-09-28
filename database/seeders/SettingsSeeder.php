<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── General / Branding ─────────────────────────────────────────────
            ['key' => 'platform_name',         'value' => 'OlympiaScholar',               'group' => 'general',   'label' => 'Platform Name'],
            ['key' => 'platform_tagline',      'value' => 'Excel Beyond Limits',           'group' => 'general',   'label' => 'Platform Tagline'],
            ['key' => 'support_email',         'value' => 'support@olympiascholar.com',    'group' => 'general',   'label' => 'Support Email'],
            ['key' => 'support_phone',         'value' => '+60 12-345 6789',               'group' => 'general',   'label' => 'Support Phone / WhatsApp'],
            ['key' => 'support_whatsapp_link', 'value' => 'https://wa.me/60123456789',     'group' => 'general',   'label' => 'WhatsApp Link'],
            ['key' => 'contact_address',       'value' => 'Kuala Lumpur, Malaysia',         'group' => 'general',   'label' => 'Business Address'],

            // ── Financial / Business Rules ─────────────────────────────────────
            ['key' => 'teacher_cut_pct',       'value' => '65',                            'group' => 'financial', 'label' => 'Teacher Revenue Cut (%)'],
            ['key' => 'currency_code',         'value' => 'MYR',                           'group' => 'financial', 'label' => 'Currency Code'],
            ['key' => 'currency_symbol',       'value' => 'RM',                            'group' => 'financial', 'label' => 'Currency Symbol'],

            // ── Platform Features ──────────────────────────────────────────────
            ['key' => 'allow_registrations',   'value' => '1',                             'group' => 'features',  'label' => 'Allow Public Registrations'],
            ['key' => 'allow_gig_claims',      'value' => '1',                             'group' => 'features',  'label' => 'Allow Teacher Gig Claiming'],
            ['key' => 'maintenance_mode',      'value' => '0',                             'group' => 'features',  'label' => 'Maintenance Mode'],
            ['key' => 'free_trial_enabled',    'value' => '1',                             'group' => 'features',  'label' => 'Free Trial Class Enabled'],

            // ── Security ───────────────────────────────────────────────────────
            ['key' => 'default_temp_password', 'value' => 'Temp@1234',                    'group' => 'security',  'label' => 'Default Temporary Password'],

            // ── Public Pricing Plans ───────────────────────────────────────────
            ['key' => 'plan_monthly_price',    'value' => 'RM 99',                         'group' => 'pricing',   'label' => 'Monthly Plan — Display Price'],
            ['key' => 'plan_monthly_per',      'value' => '/month',                         'group' => 'pricing',   'label' => 'Monthly Plan — Billing Period'],
            ['key' => 'plan_monthly_desc',     'value' => 'Access to 1 subject with live classes 4x/month', 'group' => 'pricing', 'label' => 'Monthly Plan — Description'],

            ['key' => 'plan_subject_price',    'value' => 'From RM 150',                   'group' => 'pricing',   'label' => 'Subject Package — Display Price'],
            ['key' => 'plan_subject_per',      'value' => '/subject',                       'group' => 'pricing',   'label' => 'Subject Package — Billing Period'],
            ['key' => 'plan_subject_desc',     'value' => '2-4 subjects bundled with priority support', 'group' => 'pricing', 'label' => 'Subject Package — Description'],

            ['key' => 'plan_premium_price',    'value' => 'From RM 199',                   'group' => 'pricing',   'label' => 'Premium Plan — Display Price'],
            ['key' => 'plan_premium_per',      'value' => '/month',                         'group' => 'pricing',   'label' => 'Premium Plan — Billing Period'],
            ['key' => 'plan_premium_desc',     'value' => 'Unlimited subjects, 1-on-1 tutoring and 24/7 WhatsApp support', 'group' => 'pricing', 'label' => 'Premium Plan — Description'],
        ];

        foreach ($defaults as $setting) {
            // Only insert if the key does not exist yet — never overwrite existing admin edits
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'label' => $setting['label'],
                ]
            );
        }

        // Bust cache so new values are available immediately
        Setting::clearCache();
    }
}
