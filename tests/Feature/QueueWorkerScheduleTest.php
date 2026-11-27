<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * 2026-11-27: queued emails (invoice issued, reminders, "Continue in 2027?")
 * stayed "Queued" because nothing ran queue:work on the cPanel hosts. The
 * scheduler (already run by cron) now starts a short-lived worker every minute.
 */
class QueueWorkerScheduleTest extends TestCase
{
    public function test_scheduler_runs_a_queue_worker_for_both_email_queues_every_minute(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($e) => str_contains((string) $e->command, 'queue:work'));

        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertStringContainsString('--queue=emails,default', (string) $event->command);
        $this->assertStringContainsString('--stop-when-empty', (string) $event->command);
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
