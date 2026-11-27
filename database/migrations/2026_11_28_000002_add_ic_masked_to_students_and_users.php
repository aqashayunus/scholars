<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Masked IC (e.g. "******-**-1234") next to the one-way ic_number_hash, so
 * admin can see which IC is on file (2026-11-28, Admin > Student Relationship
 * & Linkages). The full IC is still never stored. Existing rows stay empty
 * until the IC is entered again or admin verifies it.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['students', 'users'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'ic_masked')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('ic_masked', 20)->nullable()->after('ic_number_hash');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['students', 'users'] as $table) {
            if (Schema::hasColumn($table, 'ic_masked')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('ic_masked'));
            }
        }
    }
};
