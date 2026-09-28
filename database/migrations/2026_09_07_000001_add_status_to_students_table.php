<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
