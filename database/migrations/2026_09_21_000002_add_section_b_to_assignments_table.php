<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'section_b_answer_key')) {
                $table->text('section_b_answer_key')->nullable()->after('answer_key');
            }
            if (!Schema::hasColumn('assignments', 'section_b_questions')) {
                $table->integer('section_b_questions')->nullable()->default(0)->after('section_b_answer_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'section_b_answer_key')) {
                $table->dropColumn('section_b_answer_key');
            }
            if (Schema::hasColumn('assignments', 'section_b_questions')) {
                $table->dropColumn('section_b_questions');
            }
        });
    }
};
