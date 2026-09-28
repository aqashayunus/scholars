<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('qualification');
            $table->integer('experience_years')->default(0);
            $table->text('bio')->nullable();
            $table->string('teaching_style')->nullable();
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->integer('total_students')->default(0);
            $table->string('photo')->nullable();
            $table->string('specialization')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
