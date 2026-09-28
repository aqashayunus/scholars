<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create invoice_items table.
 *
 * Each row represents one line item on an invoice (one subject's fee).
 * The unit_price and amount are SNAPSHOTS taken at invoice generation time.
 * Changing subject fees later does NOT affect existing invoice items.
 * This satisfies the accounting rule: old invoices retain original pricing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // subject_id is nullable to future-proof for non-subject items (e.g., registration fees, materials)
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot of subject name at time of invoice generation
            $table->string('description');

            // Snapshot of the fee at time of invoice generation (accounting rule)
            $table->decimal('unit_price', 10, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('amount', 10, 2); // = unit_price × quantity

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
