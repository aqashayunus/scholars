<?php

namespace App\Console\Commands;

use App\Services\MonthlyBillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Daily billing housekeeping and the monthly invoice run.
 *
 *   php artisan billing:run                      # what the scheduler runs every day
 *   php artisan billing:run --month=2027-01      # issue a month's invoices now
 *   php artisan billing:run --dry-run            # show what would happen
 *
 * Every day: auto-cancel invoices 14 days past due (nothing paid) and pause
 * students who never answered "Continue?" after the reply-by date.
 * On the 1st of the month (or with --month): issue that month's invoices.
 */
class RunMonthlyBilling extends Command
{
    protected $signature = 'billing:run {--month= : Issue invoices for this month (YYYY-MM)} {--dry-run : Report only, change nothing}';

    protected $description = 'Monthly invoices for confirmed students, 14-day auto-cancel, and pausing unanswered renewals';

    public function handle(MonthlyBillingService $billing): int
    {
        $dry = (bool) $this->option('dry-run');
        $monthOpt = $this->option('month');

        if (is_string($monthOpt) && $monthOpt !== '') {
            try {
                $month = Carbon::createFromFormat('Y-m', $monthOpt)->startOfMonth();
            } catch (\Throwable) {
                $this->error('Use --month=YYYY-MM, e.g. --month=2027-01');

                return self::FAILURE;
            }
        } else {
            $month = now()->day === 1 ? now()->startOfMonth() : null;
        }

        $cancel = $billing->autoCancelUnpaid(null, $dry);
        $this->line('Auto-cancel (unpaid '.MonthlyBillingService::GRACE_DAYS.' days past due): '.count($cancel['cancelled']).' cancelled');
        foreach ($cancel['cancelled'] as $c) {
            $this->line("  - {$c}");
        }
        foreach ($cancel['needs_admin'] as $n) {
            $this->warn("  ! needs admin: {$n}");
        }

        $paused = $billing->closeUnanswered(null, $dry);
        $this->line('No reply / not continuing after the reply-by date: '.count($paused).' paused');
        foreach ($paused as $p) {
            $this->line("  - {$p}");
        }

        $stopped = $billing->closeStopped(null, $dry);
        $this->line('Stopped families whose last month has ended: '.count($stopped).' paused');
        foreach ($stopped as $p) {
            $this->line("  - {$p}");
        }

        if ($month) {
            $run = $billing->run($month, $dry);
            if ($run['skipped_reason']) {
                $this->warn("Monthly invoices {$run['month']}: skipped. {$run['skipped_reason']}");
            } else {
                $this->line("Monthly invoices {$run['month']}: ".count($run['issued']).' issued, '.count($run['already']).' already had one, '.count($run['failed']).' not billed');
                foreach ($run['issued'] as $i) {
                    $this->line("  + {$i}");
                }
                foreach ($run['failed'] as $f) {
                    $this->warn("  ! {$f}");
                }
            }
        }

        return self::SUCCESS;
    }
}
