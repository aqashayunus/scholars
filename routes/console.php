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

// ── Storage: Sync files from storage/app/public to public/storage (useful for cPanel environments) ─
Artisan::command('storage:sync', function () {
    $source = storage_path('app/public');
    $dest = public_path('storage');

    if (!is_dir($source)) {
        $this->error("Source directory {$source} does not exist.");
        return 1;
    }

    if (is_link($dest)) {
        $this->info("public/storage is already a symbolic link. No file copying required.");
        return 0;
    }

    if (!is_dir($dest)) {
        @mkdir($dest, 0755, true);
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $copied = 0;
    foreach ($files as $file) {
        $rel = substr($file->getPathname(), strlen($source) + 1);
        $target = $dest . DIRECTORY_SEPARATOR . $rel;

        if ($file->isDir()) {
            if (!is_dir($target)) {
                @mkdir($target, 0755, true);
            }
        } else {
            $targetDir = dirname($target);
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            if (!file_exists($target) || filemtime($file->getPathname()) > filemtime($target)) {
                @copy($file->getPathname(), $target);
                @chmod($target, 0644);
                $copied++;
            }
        }
    }

    $this->info("Storage sync complete. Copied/updated {$copied} files to public/storage.");
    return 0;
})->purpose('Synchronize storage/app/public files into public/storage for cPanel/shared hosting without symlink support');


