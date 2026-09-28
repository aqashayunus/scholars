<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add application_status to teachers table if not already present
        if (!Schema::hasColumn('teachers', 'application_status')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('application_status', 20)->default('active')->after('id');
            });

            // All existing teachers are already active (they have been verified previously)
            DB::table('teachers')->update(['application_status' => 'active']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'application_status')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('application_status');
            });
        }
    }
};
