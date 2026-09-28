<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create invoice_reminders table.
 *
 * Tracks every scheduled and sent email reminder for each invoice.
 * The unique constraint on (invoice_id, reminder_type, recipient_email)
 * prevents duplicate reminders even if the scheduler runs multiple times.
 *
 * Reminder types (as defined in spec):
 *   invoice_issued  — immediately after invoice creation
 *   due_7_days      — 7 days before due date
 *   due_3_days      — 3 days before due date
 *   due_today       — on the due date
 *   overdue_1_day   — 1 day after due date
 *   overdue_3_days  — 3 days after due date
 *   overdue_7_days  — 7 days after due date
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            $table->enum('reminder_type', [
                'invoice_issued',
                'due_7_days',
                'due_3_days',
                'due_today',
                'overdue_1_day',
                'overdue_3_days',
                'overdue_7_days',
            ]);

            $table->string('recipient_email');
            $table->enum('recipient_type', ['student', 'parent']);

            // When this reminder is scheduled to be sent
            $table->datetime('scheduled_at');

            // When it was actually sent (null = not yet sent)
            $table->datetime('sent_at')->nullable();

            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');

            // Error details if sending failed
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Prevent duplicate reminders for same invoice + type + recipient
            $table->unique(['invoice_id', 'reminder_type', 'recipient_email'], 'invoice_reminders_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
