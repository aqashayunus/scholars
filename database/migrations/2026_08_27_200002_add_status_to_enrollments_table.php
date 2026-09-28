<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add if column doesn't exist (safe re-run)
        if (!Schema::hasColumn('enrollments', 'status')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('course_id');
            });

            // All existing enrollments default to 'active'
            DB::table('enrollments')->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('enrollments', 'status')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
