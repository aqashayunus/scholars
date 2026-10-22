<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add subject_id to assignments (so an assignment can belong to a subject)
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('subject_id')
                  ->nullable()
                  ->after('course_id')
                  ->constrained('subjects')
                  ->nullOnDelete();
        });

        // Step 2: Add diagnostic_assignment_id to subjects (pointer to the one diagnostic test)
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('diagnostic_assignment_id')
                  ->nullable()
                  ->after('is_active')
                  ->constrained('assignments')
                  ->nullOnDelete();

            // Allow admin to override default duration per subject
            $table->unsignedSmallInteger('diagnostic_duration_minutes')
                  ->nullable()
                  ->after('diagnostic_assignment_id')
                  ->comment('Admin-configurable diagnostic test duration in minutes (default 20)');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['diagnostic_assignment_id']);
            $table->dropColumn(['diagnostic_assignment_id', 'diagnostic_duration_minutes']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
