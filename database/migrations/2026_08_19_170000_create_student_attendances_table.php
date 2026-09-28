<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('live_class_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['attended', 'absent', 'late', 'excused'])->default('attended');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'live_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
