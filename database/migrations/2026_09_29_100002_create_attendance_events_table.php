<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_attendance_id')->nullable()->constrained('student_attendances')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('live_class_id')->constrained('live_classes')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 32)->default('system'); // student, teacher, admin, system
            $table->string('event_type', 64); // join_attempt, rejoin_attempt, teacher_marked_present, teacher_marked_late, teacher_marked_absent, teacher_marked_excused, attendance_verified, correction_requested, correction_reviewed
            $table->timestamp('event_timestamp')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'live_class_id']);
            $table->index('event_type');
            $table->index('event_timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
