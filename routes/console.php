<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Billing: Daily Invoice Reminder Processing ─────────────────────────────
// Runs every day at 08:00 AM (server local time).
// Marks overdue invoices and dispatches scheduled email reminders to the queue.
// IMPORTANT: On Windows/XAMPP, you must register `php artisan schedule:run` with
// Windows Task Scheduler to run every minute, or run manually for testing.
//
// Windows Task Scheduler setup:
//   Program:  C:\xampp\php\php.exe
//   Arguments: C:\xampp\htdocs\olympiascholar\artisan schedule:run
//   Schedule:  Every 1 minute
//
// Billing: monthly invoices on the 1st, plus daily 14-day auto-cancel and
// pausing unanswered "Continue?" renewals. Runs before the 08:00 reminders.
Schedule::command('billing:run')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/billing-run.log'));

Schedule::command('invoices:process-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/billing-reminders.log'));

// ── Communication: Process Scheduled Email Campaigns every 5 minutes ─────────
Schedule::command('emails:process-scheduled-campaigns')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/email-campaigns.log'));

// ── Communication: Process Upcoming Class Reminders hourly ────────────────────
Schedule::command('emails:process-class-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/class-reminders.log'));
