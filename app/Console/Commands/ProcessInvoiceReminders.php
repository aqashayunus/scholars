<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use App\Services\ReminderService;
use Illuminate\Console\Command;

class ProcessInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:process-reminders
                            {--dry-run : Show what would be sent without actually dispatching jobs}';

    /**
     * The console command description.
     */
    protected $description = 'Mark overdue invoices and dispatch scheduled invoice reminder emails to the queue.';

    public function __construct(
        private InvoiceService  $invoiceService,
        private ReminderService $reminderService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('[Scholars Billing] Processing invoice reminders — ' . now()->toDateTimeString());

        if ($isDryRun) {
            $this->warn('DRY RUN MODE — no emails will be dispatched.');
        }

        // Step 1: Mark any overdue invoices
        $this->line('→ Checking for overdue invoices...');
        if (!$isDryRun) {
            $overdueCount = $this->invoiceService->markOverdueInvoices();
            $this->line("  Marked {$overdueCount} invoice(s) as OVERDUE.");
        } else {
            $this->line('  [dry-run] Skipped marking overdue invoices.');
        }

        // Step 2: Dispatch due reminder emails
        $this->line('→ Dispatching due reminder emails...');
        if (!$isDryRun) {
            $dispatched = $this->reminderService->dispatchDueReminders();
            $this->line("  Dispatched {$dispatched} reminder job(s) to the queue.");
        } else {
            $this->line('  [dry-run] Skipped dispatching reminder jobs.');
        }

        $this->info('[Scholars Billing] Done.');

        return Command::SUCCESS;
    }
}
