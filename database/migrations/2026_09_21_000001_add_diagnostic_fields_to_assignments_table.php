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
            if (!Schema::hasColumn('assignments', 'answer_key')) {
                $table->text('answer_key')->nullable()->after('instructions');
            }
            if (!Schema::hasColumn('assignments', 'total_questions')) {
                $table->integer('total_questions')->nullable()->default(10)->after('answer_key');
            }
            if (!Schema::hasColumn('assignments', 'time_limit_minutes')) {
                $table->integer('time_limit_minutes')->nullable()->default(20)->after('total_questions');
            }
        });

        Schema::table('student_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_assignments', 'answers_json')) {
                $table->text('answers_json')->nullable()->after('submission_notes');
            }
            if (!Schema::hasColumn('student_assignments', 'tahap_kepandaian')) {
                $table->string('tahap_kepandaian', 50)->nullable()->after('score');
            }
            if (!Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
                $table->integer('time_taken_seconds')->nullable()->after('tahap_kepandaian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'answer_key')) {
                $table->dropColumn('answer_key');
            }
            if (Schema::hasColumn('assignments', 'total_questions')) {
                $table->dropColumn('total_questions');
            }
            if (Schema::hasColumn('assignments', 'time_limit_minutes')) {
                $table->dropColumn('time_limit_minutes');
            }
        });

        Schema::table('student_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('student_assignments', 'answers_json')) {
                $table->dropColumn('answers_json');
            }
            if (Schema::hasColumn('student_assignments', 'tahap_kepandaian')) {
                $table->dropColumn('tahap_kepandaian');
            }
            if (Schema::hasColumn('student_assignments', 'time_taken_seconds')) {
                $table->dropColumn('time_taken_seconds');
            }
        });
    }
};
