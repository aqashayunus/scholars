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
Schedule::command('invoices:process-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/billing-reminders.log'));
