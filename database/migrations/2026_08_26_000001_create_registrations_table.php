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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('register_as')->default('student');
            $table->string('student_name');
            $table->string('parent_name')->nullable();
            $table->string('educator_name')->nullable();
            $table->string('school_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('programme');
            $table->string('level')->nullable();
            $table->text('subjects')->nullable();
            $table->string('schedule')->nullable();
            $table->string('promo_code')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
