<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create payment_invoices pivot table.
 *
 * Represents the allocation of a single payment (checkout transaction)
 * to one or more individual invoices.
 *
 * Example:
 *   Parent pays RM610 for Ahmad (RM360) and Aisyah (RM250).
 *   → One payments record (RM610 total)
 *   → Two payment_invoices records:
 *       payment_id → Ahmad's invoice, amount_applied = 360
 *       payment_id → Aisyah's invoice, amount_applied = 250
 *
 * Invoices remain separate. The payment transaction is shared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Amount from this payment applied to this specific invoice
            $table->decimal('amount_applied', 10, 2);

            $table->timestamps();

            // Prevent duplicate allocation of same payment to same invoice
            $table->unique(['payment_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};
