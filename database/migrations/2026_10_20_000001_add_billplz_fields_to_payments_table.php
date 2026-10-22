<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'billplz_bill_id')) {
                $table->string('billplz_bill_id', 100)->nullable()->after('gateway_transaction_id')->index();
            }
            if (!Schema::hasColumn('payments', 'billplz_url')) {
                $table->string('billplz_url', 500)->nullable()->after('billplz_bill_id');
            }
            if (!Schema::hasColumn('payments', 'gateway_payload')) {
                $table->json('gateway_payload')->nullable()->after('payment_proof_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['billplz_bill_id', 'billplz_url', 'gateway_payload'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
