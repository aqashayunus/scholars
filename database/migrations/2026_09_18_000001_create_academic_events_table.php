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
        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', [
                'public_holiday',
                'academic_term',
                'school_break',
                'exam_period',
                'special_event',
            ])->default('public_holiday');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_all_day')->default(true);
            $table->string('color', 30)->nullable(); // e.g. #ef4444, #f59e0b, #10b981
            $table->string('state_or_region')->nullable(); // null = nationwide
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
