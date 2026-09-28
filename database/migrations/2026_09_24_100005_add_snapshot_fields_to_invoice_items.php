<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1e — Add snapshot columns to invoice_items.
 *
 * These columns preserve a point-in-time snapshot of the billing component
 * details at the time the invoice was issued.
 *
 * This is critical for historical invoice immutability:
 * even if the fee structure's items are later changed, old invoices
 * retain the values that were in force when they were generated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('item_type', 50)->nullable()->after('description');
            $table->string('package_name', 255)->nullable()->after('item_type');
            $table->string('billing_frequency', 50)->nullable()->after('package_name');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'package_name', 'billing_frequency']);
        });
    }
};
