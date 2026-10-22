<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\Email\EmailSendingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledEmailCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'emails:process-scheduled-campaigns';

    /**
     * The console command description.
     */
    protected $description = 'Scan and dispatch scheduled email campaigns whose scheduled_at time has arrived';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $dueCampaigns = EmailCampaign::where('status', EmailCampaign::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', $now)
            ->get();

        $count = $dueCampaigns->count();
        $this->info("Found {$count} scheduled campaign(s) ready for sending.");

        foreach ($dueCampaigns as $campaign) {
            $this->line("Dispatching campaign #{$campaign->id}: '{$campaign->name}'...");
            EmailSendingService::dispatchCampaign($campaign);
            Log::info("[ProcessScheduledEmailCampaigns] Dispatched scheduled campaign #{$campaign->id} ({$campaign->name}).");
        }

        $this->info("Done.");
        return Command::SUCCESS;
    }
}
