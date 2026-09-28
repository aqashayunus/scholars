<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('type')->default('Notes'); // Notes, Worksheet, Slides, Document, Assignment, Past Paper
            $table->string('topic')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0); // in bytes
            $table->string('file_type', 20)->default('pdf'); // extension e.g. pdf, docx, pptx
            $table->string('visibility')->default('all'); // all, selected
            $table->string('status')->default('Published'); // Published, Draft, Scheduled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
    }
};
