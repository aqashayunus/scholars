<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renewals moved from its own admin tab into Financials & Sales >
 * Renewals & Subscriptions (2026-11-28). Update the "trigger" description
 * shown in Email Settings, only where it still has the original wording.
 */
return new class extends Migration
{
    private const TEXT = [
        'renewal_invitation' => [
            'When admin sends "Continue in {school year}?" from Admin > Renewals',
            'When admin sends "Continue in {school year}?" from Financials & Sales > Renewals & Subscriptions',
        ],
        'renewal_reminder' => [
            'When admin sends a reminder from Admin > Renewals',
            'When admin sends a reminder from Financials & Sales > Renewals & Subscriptions',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('email_automations')) {
            return;
        }
        foreach (self::TEXT as $key => [$old, $new]) {
            DB::table('email_automations')->where('key', $key)->where('trigger_event', $old)->update(['trigger_event' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::TEXT as $key => [$old, $new]) {
            DB::table('email_automations')->where('key', $key)->where('trigger_event', $new)->update(['trigger_event' => $old]);
        }
    }
};
