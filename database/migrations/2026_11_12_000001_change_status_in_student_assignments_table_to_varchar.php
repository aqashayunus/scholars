<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * student_assignments.status was a native MySQL ENUM('pending','submitted','graded','late')
 * but App\Enums\StudentAssignmentStatus::PendingReview ('pending_review') is now written to
 * it by the diagnostic grading engine — an invalid ENUM member causes SQLSTATE[01000]: 1265
 * data truncation on write. Widen the column so every backed-enum case is valid.
 *
 * down() reverts to the narrower ENUM; any existing 'pending_review' rows would be truncated
 * by MySQL on rollback, same tradeoff the equivalent fix on main accepts.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_assignments')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE student_assignments MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'pending'");
            } else {
                Schema::table('student_assignments', function (Blueprint $table) {
                    $table->string('status', 30)->default('pending')->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_assignments') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_assignments MODIFY COLUMN status ENUM('pending', 'submitted', 'graded', 'late') NOT NULL DEFAULT 'pending'");
        }
    }
};
