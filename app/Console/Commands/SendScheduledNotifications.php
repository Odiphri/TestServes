<?php

namespace App\Console\Commands;

use App\Jobs\DispatchNotificationCampaign;
use App\Models\NotificationCampaign;
use App\Services\Notifications\RecipientResolver;
use App\Support\TenantDatabaseManager;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';

    protected $description = 'Send due scheduled notification campaigns.';

    public function handle(RecipientResolver $recipients, TenantDatabaseManager $tenants): int
    {
        $campaigns = NotificationCampaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        foreach ($campaigns as $campaign) {
            (new DispatchNotificationCampaign($campaign->id))->handle($recipients, $tenants);
            $this->line("Sent campaign {$campaign->id}: {$campaign->title}");
        }

        $this->info("Processed {$campaigns->count()} scheduled notification campaign(s).");

        return self::SUCCESS;
    }
}
