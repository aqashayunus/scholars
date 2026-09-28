<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('live_classes', function (Blueprint $table) {
            $table->boolean('is_rescheduled')->default(false)->after('status');
            $table->dateTime('original_scheduled_at')->nullable()->after('is_rescheduled');
            $table->text('reschedule_reason')->nullable()->after('original_scheduled_at');
            $table->foreignId('rescheduled_by')->nullable()->after('reschedule_reason')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropForeign(['rescheduled_by']);
            $table->dropColumn(['is_rescheduled', 'original_scheduled_at', 'reschedule_reason', 'rescheduled_by']);
        });
    }
};
