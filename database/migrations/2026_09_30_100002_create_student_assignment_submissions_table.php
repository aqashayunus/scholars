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
        if (!Schema::hasTable('student_assignment_submissions')) {
            Schema::create('student_assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_assignment_id')->unique()->constrained('student_assignments')->onDelete('cascade');
                $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->enum('status', ['draft', 'submitted', 'graded', 'returned'])->default('draft');
                $table->unsignedInteger('current_page')->default(1);
                $table->decimal('zoom_level', 4, 2)->default(1.00);
                $table->unsignedInteger('total_pages')->default(1);
                $table->unsignedInteger('version')->default(1);
                $table->string('final_pdf_path', 255)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('last_saved_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->index(['student_id', 'status'], 'idx_sub_student_status');
                $table->index('assignment_id', 'idx_sub_assignment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assignment_submissions');
    }
};
