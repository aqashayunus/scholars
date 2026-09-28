<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_gigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->enum('gig_type', ['substitute_sos', 'scheduled_class', 'private_tutoring', 'exam_workshop'])->default('scheduled_class');
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->decimal('payout_amount', 8, 2)->default(50.00);
            $table->decimal('surge_bonus', 8, 2)->default(0.00);
            $table->enum('status', ['open', 'claimed', 'checked_in', 'completed', 'cancelled'])->default('open');
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_gigs');
    }
};
