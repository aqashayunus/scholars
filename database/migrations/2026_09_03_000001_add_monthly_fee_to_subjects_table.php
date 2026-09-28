<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Admin-configurable monthly fee per subject.
            // Nullable so existing subjects are not broken.
            // Authoritative pricing: snapshotted into invoice_items.amount at invoice generation.
            $table->decimal('monthly_fee', 10, 2)->nullable()->after('is_active')
                  ->comment('Admin-configurable monthly fee for this subject. Snapshotted into invoice_items at invoice generation.');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('monthly_fee');
        });
    }
};
