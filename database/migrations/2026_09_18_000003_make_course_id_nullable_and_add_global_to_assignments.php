<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->change();

            if (!Schema::hasColumn('assignments', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('course_id');
            }

            if (!Schema::hasColumn('assignments', 'target_level')) {
                $table->string('target_level')->nullable()->after('is_global');
            }

            if (!Schema::hasColumn('assignments', 'created_by_admin')) {
                $table->boolean('created_by_admin')->default(false)->after('target_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'created_by_admin')) {
                $table->dropColumn('created_by_admin');
            }
            if (Schema::hasColumn('assignments', 'target_level')) {
                $table->dropColumn('target_level');
            }
            if (Schema::hasColumn('assignments', 'is_global')) {
                $table->dropColumn('is_global');
            }
            $table->foreignId('course_id')->nullable(false)->change();
        });
    }
};
