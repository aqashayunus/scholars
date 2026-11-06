<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum('pending', 'submitted', 'graded', 'late') to varchar(30)
        // to prevent 'Data truncated for column status' when setting 'pending_review', 'completed', etc.
        if (Schema::hasTable('student_assignments')) {
            DB::statement("ALTER TABLE student_assignments MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_assignments')) {
            DB::statement("ALTER TABLE student_assignments MODIFY COLUMN status ENUM('pending', 'submitted', 'graded', 'late') NOT NULL DEFAULT 'pending'");
        }
    }
};
