<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('level', ['SPM', 'IGCSE', 'STPM', 'Foundation'])->default('SPM');
            $table->decimal('price', 8, 2)->default(99.00);
            $table->enum('status', ['published', 'draft', 'archived'])->default('published');
            $table->integer('total_lessons')->default(0);
            $table->string('schedule')->nullable(); // e.g. "Mon & Wed 8–9 PM"
            $table->string('thumbnail')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->primary(['teacher_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
        Schema::dropIfExists('courses');
    }
};
