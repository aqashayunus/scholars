<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1b — Create fee_structure_subjects pivot table.
 *
 * Associates subjects to fee structures explicitly, replacing the implicit
 * category-based logic currently used in InvoiceService.
 *
 * is_required: true  = subject is always included (fixed bundle subjects)
 *              false = subject is in the allowed pool (pick_n / tiered)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->boolean('is_required')->default(true);   // required = must-include subject
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // A subject can only appear once per fee structure
            $table->unique(['fee_structure_id', 'subject_id']);

            // Indexes for efficient lookups
            $table->index('fee_structure_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_subjects');
    }
};
