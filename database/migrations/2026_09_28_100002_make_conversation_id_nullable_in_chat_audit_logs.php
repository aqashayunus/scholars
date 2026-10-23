<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('chat_audit_logs')) {
            Schema::table('chat_audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('conversation_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('chat_audit_logs')) {
            Schema::table('chat_audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('conversation_id')->nullable(false)->change();
            });
        }
    }
};
