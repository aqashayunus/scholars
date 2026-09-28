<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1a — Extend fee_structures with package engine columns.
 *
 * Existing columns are preserved for full backward compatibility:
 *   name, description, amount, education_level, frequency, is_active, student_id
 *
 * New columns added:
 *   package_type      — the pricing model for this fee structure
 *   minimum_subjects  — for pick_n: minimum subjects student must select
 *   maximum_subjects  — for pick_n: maximum subjects student may select
 *   effective_from    — package validity start date (null = always valid)
 *   effective_until   — package validity end date (null = no expiry)
 *   status            — draft/active/inactive/archived (replaces boolean is_active for richer state)
 *   version           — version number (1,2,3,…) for versioned pricing
 *   parent_fee_structure_id — points to the original package this is a version of
 *   is_stackable_discount  — whether promos/legacy discount can stack on top
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Package type — determines the pricing calculation strategy
            $table->enum('package_type', [
                'individual',    // Normal flat price, no subject constraint
                'fixed_bundle',  // Exact predefined set of subjects
                'pick_n',        // Student picks min–max subjects from allowed pool
                'tiered',        // Price depends on subject count
                'custom',        // Admin-controlled special pricing
            ])->default('individual')->after('frequency');

            // Pick-N constraints
            $table->unsignedTinyInteger('minimum_subjects')->nullable()->after('package_type');
            $table->unsignedTinyInteger('maximum_subjects')->nullable()->after('minimum_subjects');

            // Validity window (null = unlimited)
            $table->date('effective_from')->nullable()->after('maximum_subjects');
            $table->date('effective_until')->nullable()->after('effective_from');

            // Richer status (replaces boolean is_active for new packages, backward-compatible)
            // is_active remains for legacy; status drives the new engine
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('active')->after('effective_until');

            // Versioning support
            $table->unsignedSmallInteger('version')->default(1)->after('status');
            $table->foreignId('parent_fee_structure_id')
                ->nullable()
                ->after('version')
                ->constrained('fee_structures')
                ->nullOnDelete();

            // Discount stacking policy
            $table->boolean('is_stackable_discount')->default(false)->after('parent_fee_structure_id');

            // Indexes for common queries
            $table->index(['package_type', 'status', 'is_active']);
            $table->index(['education_level', 'status']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['parent_fee_structure_id']);
            $table->dropIndex(['package_type', 'status', 'is_active']);
            $table->dropIndex(['education_level', 'status']);
            $table->dropIndex(['effective_from', 'effective_until']);
            $table->dropColumn([
                'package_type',
                'minimum_subjects',
                'maximum_subjects',
                'effective_from',
                'effective_until',
                'status',
                'version',
                'parent_fee_structure_id',
                'is_stackable_discount',
            ]);
        });
    }
};
