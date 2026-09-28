<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add soft delete support to fee_structures.
 *
 * This replaces the hard-delete pattern with soft deletion, ensuring:
 * - Fee structures referenced by historical invoices remain traceable
 * - Invoice relationships are preserved even after admin removes a package
 * - The admin can "restore" a soft-deleted package if needed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
