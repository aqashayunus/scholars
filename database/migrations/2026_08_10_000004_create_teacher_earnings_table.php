<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('teaching_gig_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('base_amount', 8, 2);
            $table->decimal('bonus_amount', 8, 2)->default(0.00);
            $table->decimal('total_amount', 8, 2);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('approved');
            $table->string('payout_batch_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_earnings');
    }
};
