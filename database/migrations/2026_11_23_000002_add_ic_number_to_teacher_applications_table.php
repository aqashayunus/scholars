<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IC / Passport number captured on the public teacher application form.
 *
 * Guarded because olympianscholars.com already has this exact column from
 * the retired pre-2026-11-18 main branch (its migration
 * 2026_10_23_000001_add_ic_number_to_teacher_applications_table), holding
 * real applicants' values: there this is a no-op and the data is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_applications') && ! Schema::hasColumn('teacher_applications', 'ic_number')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                $table->string('ic_number', 50)->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teacher_applications', 'ic_number')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                $table->dropColumn('ic_number');
            });
        }
    }
};
