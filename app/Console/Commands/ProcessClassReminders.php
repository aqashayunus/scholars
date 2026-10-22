<?php

namespace App\Console\Commands;

use App\Models\LiveClass;
use App\Services\Email\EmailAutomationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessClassReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'emails:process-class-reminders
                            {--hours=24 : Advance window in hours to look for upcoming classes}
                            {--dry-run : Preview matching reminders without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Scan upcoming live classes within the advance window and trigger automated class reminder emails.';

    public function handle(): int
    {
        $advanceHours = (int) $this->option('hours');
        $isDryRun = (bool) $this->option('dry-run');

        $now = now();
        $windowEnd = now()->addHours($advanceHours);

        $this->info("[Olympia Email] Processing upcoming class reminders (Window: next {$advanceHours}h) — {$now->toDateTimeString()}");

        $upcomingClasses = LiveClass::with(['course.enrollments.student.user', 'course.subject', 'teacher.user'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('scheduled_at', [$now, $windowEnd])
            ->get();

        if ($upcomingClasses->isEmpty()) {
            $this->line('  No upcoming classes found within window.');
            return Command::SUCCESS;
        }

        $dispatched = 0;

        foreach ($upcomingClasses as $class) {
            $course = $class->course;
            if (!$course) continue;

            $enrollments = $course->enrollments ?? collect();

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                if (!$student) continue;

                $idempotencyKey = "class_{$class->id}_student_{$student->id}_reminder";

                if ($isDryRun) {
                    $this->line("  [dry-run] Would trigger reminder for Student #{$student->id} ({$student->user?->name}) — Class: {$class->title}");
                    $dispatched++;
                    continue;
                }

                try {
                    $triggered = EmailAutomationService::trigger(
                        'upcoming_class_reminder',
                        [
                            'live_class' => $class,
                            'course'     => $course,
                            'student'    => $student,
                            'user'       => $student->user,
                        ],
                        $idempotencyKey
                    );

                    if ($triggered) {
                        $dispatched++;
                    }
                } catch (\Throwable $e) {
                    Log::error("[ProcessClassReminders] Failed for class #{$class->id}, student #{$student->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("  Processed: {$dispatched} reminder(s) dispatched.");
        return Command::SUCCESS;
    }
}
