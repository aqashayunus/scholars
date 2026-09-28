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
            if (!Schema::hasColumn('assignments', 'type')) {
                $table->string('type')->default('Assignment')->after('title');
            }
            if (!Schema::hasColumn('assignments', 'topic')) {
                $table->string('topic')->nullable()->after('type');
            }
            if (!Schema::hasColumn('assignments', 'instructions')) {
                $table->text('instructions')->nullable()->after('description');
            }
            if (!Schema::hasColumn('assignments', 'total_marks')) {
                $table->integer('total_marks')->default(100)->after('due_date');
            }
            if (!Schema::hasColumn('assignments', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('total_marks');
            }
            if (!Schema::hasColumn('assignments', 'exam_date')) {
                $table->date('exam_date')->nullable()->after('duration_minutes');
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('quizzes', 'time_limit_minutes')) {
                $table->integer('time_limit_minutes')->default(15)->after('total_questions');
            }
            if (!Schema::hasColumn('quizzes', 'passing_marks')) {
                $table->integer('passing_marks')->default(60)->after('time_limit_minutes');
            }
            if (!Schema::hasColumn('quizzes', 'due_date')) {
                $table->date('due_date')->nullable()->after('passing_marks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['type', 'topic', 'instructions', 'total_marks', 'duration_minutes', 'exam_date']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['description', 'time_limit_minutes', 'passing_marks', 'due_date']);
        });
    }
};
