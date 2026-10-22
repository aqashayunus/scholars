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
        if (!Schema::hasTable('submission_pages')) {
            Schema::create('submission_pages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('student_assignment_submissions')->onDelete('cascade');
                $table->unsignedInteger('page_number');
                $table->longText('annotation_json')->nullable();
                $table->unsignedInteger('stroke_count')->default(0);
                $table->unsignedInteger('page_version')->default(1);
                $table->timestamps();

                $table->unique(['submission_id', 'page_number'], 'uq_sub_page');
                $table->index(['submission_id', 'page_number'], 'idx_sub_page_num');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_pages');
    }
};
