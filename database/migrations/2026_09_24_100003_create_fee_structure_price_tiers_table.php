<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1c — Create fee_structure_price_tiers table.
 *
 * Tiered pricing: price depends on how many subjects the student selects.
 *
 * Example:
 *   min=1, max=1, price=90.00
 *   min=2, max=2, price=160.00
 *   min=3, max=3, price=210.00
 *   min=4, max=4, price=260.00
 *
 * The PackagePricingService validates that tiers are non-overlapping and
 * cover the subject range advertised by the package.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->unsignedTinyInteger('minimum_subjects');
            $table->unsignedTinyInteger('maximum_subjects');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['fee_structure_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_price_tiers');
    }
};
