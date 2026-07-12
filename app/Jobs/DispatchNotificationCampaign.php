<?php

namespace App\Jobs;

use App\Models\NotificationCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchNotificationCampaign implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(): void
    {
        $campaign = NotificationCampaign::find($this->campaignId);

        if (! $campaign || $campaign->status !== 'queued') {
            return;
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'successful_deliveries' => $campaign->recipients()->whereNotNull('delivered_at')->count(),
            'failed_deliveries' => $campaign->recipients()->whereNotNull('failed_at')->count(),
        ]);
    }
}
