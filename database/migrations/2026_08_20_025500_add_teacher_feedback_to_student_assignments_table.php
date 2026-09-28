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
        Schema::table('student_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_assignments', 'teacher_feedback')) {
                $table->text('teacher_feedback')->nullable()->after('submission_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('student_assignments', 'teacher_feedback')) {
                $table->dropColumn('teacher_feedback');
            }
        });
    }
};
