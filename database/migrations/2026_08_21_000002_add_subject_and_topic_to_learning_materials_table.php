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
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('course_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('topic_id')->nullable()->after('subject_id')->constrained('subject_topics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['topic_id', 'subject_id']);
        });
    }
};
