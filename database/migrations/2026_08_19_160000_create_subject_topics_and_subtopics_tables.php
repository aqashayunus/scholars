<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('chapter_number')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('subject_topics')->onDelete('cascade');
            $table->string('title');
            $table->enum('default_status', ['Mastered', 'In Progress', 'Needs Practice', 'Not Started'])->default('Not Started');
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        Schema::create('student_subtopic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subtopic_id')->constrained('subject_subtopics')->onDelete('cascade');
            $table->enum('status', ['Mastered', 'In Progress', 'Needs Practice', 'Not Started'])->default('Not Started');
            $table->timestamps();
            $table->unique(['student_id', 'subtopic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subtopic_progress');
        Schema::dropIfExists('subject_subtopics');
        Schema::dropIfExists('subject_topics');
    }
};
