<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1d — Create fee_structure_items table.
 *
 * Multi-component billing: a package can have multiple distinct fee components
 * with different billing frequencies.
 *
 * Example:
 *   Tuition Fee       RM250   monthly
 *   Annual Materials   RM80   annual
 *   Registration       RM50   one_time
 *   Deposit           RM100   one_time
 *
 * The `amount` column in fee_structures remains for legacy compatibility and
 * acts as the base tuition amount when items are not configured.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('item_type', [
                'registration',
                'tuition',
                'material',
                'resource',
                'assessment',
                'deposit',
                'other',
            ])->default('tuition');
            $table->decimal('amount', 10, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->enum('billing_frequency', [
                'one_time',
                'monthly',
                'term',
                'annual',
            ])->default('monthly');
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_refundable')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['fee_structure_id', 'sort_order']);
            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_items');
    }
};
