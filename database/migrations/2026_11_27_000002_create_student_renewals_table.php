<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per student per school year: the "Continue in 2027?" answer and
 * the monthly billing window that follows from it (decision 2026-11-27).
 *
 * status: invited -> confirmed | declined | lapsed (no reply by the reply-by
 * date, or an invoice left unpaid 14 days past due); confirmed -> stopped
 * (family/admin chose "stop after this month").
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_renewals')) {
            return;
        }

        Schema::create('student_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('school_year', 9);                       // e.g. "2027"
            $table->string('status', 20)->default('invited')->index();
            $table->string('token', 64)->unique();                  // for the email link
            $table->string('level', 50)->nullable();                // confirmed Form / Year
            $table->json('subject_ids')->nullable();                // confirmed subjects
            $table->foreignId('fee_structure_id')->nullable()->constrained()->nullOnDelete();
            $table->date('billing_starts')->nullable();              // first billed month (1st of month)
            $table->date('stops_after')->nullable();                 // last billed month (1st of month)
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paused_at')->nullable();
            $table->string('pause_reason')->nullable();
            $table->foreignId('first_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'school_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_renewals');
    }
};
