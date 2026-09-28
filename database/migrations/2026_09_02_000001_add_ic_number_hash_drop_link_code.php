<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ic_number_hash to students table and drop link_code
        Schema::table('students', function (Blueprint $table) {
            // IC number stored as SHA-256 hash — cannot be retrieved, only verified
            $table->string('ic_number_hash', 64)->nullable()->unique()->after('user_id');
            $table->string('gender', 10)->nullable()->after('ic_number_hash');
            $table->string('nationality', 50)->nullable()->after('gender');
            $table->string('state', 50)->nullable()->after('nationality');
            $table->date('date_of_birth')->nullable()->after('state');
        });

        // Drop link_code from students (no longer used)
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['link_code']);
            $table->dropColumn('link_code');
        });

        // Add ic_number_hash to users table (for parent identity)
        Schema::table('users', function (Blueprint $table) {
            $table->string('ic_number_hash', 64)->nullable()->unique()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['ic_number_hash', 'gender', 'nationality', 'state', 'date_of_birth']);
            $table->string('link_code', 16)->nullable()->unique()->after('total_points');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ic_number_hash');
        });
    }
};
