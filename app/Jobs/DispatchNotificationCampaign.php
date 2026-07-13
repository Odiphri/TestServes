<?php

namespace App\Jobs;

use App\Models\NotificationCampaign;
use App\Services\Notifications\RecipientResolver;
use App\Support\TenantDatabaseManager;

class DispatchNotificationCampaign
{
    public function __construct(public int $campaignId)
    {
    }

    public function handle(RecipientResolver $resolver, TenantDatabaseManager $tenants): void
    {
        $campaign = NotificationCampaign::on('mysql')->find($this->campaignId);

        if (! $campaign || ! in_array($campaign->status, ['queued', 'scheduled'], true)) {
            return;
        }

        $admin = $campaign->creator;
        if (! $admin) {
            $campaign->update(['status' => 'failed']);

            return;
        }

        $campaign->update(['status' => 'sending']);

        $targets = $resolver->resolve($admin, $campaign->recipient_scope, $campaign->recipient_payload ?? []);
        $jobs = $targets
            ->chunk(200)
            ->map(fn ($chunk) => new DeliverNotificationBatch($campaign->id, $chunk->map->toArray()->all()))
            ->all();

        if ($jobs === []) {
            $campaign->update(['status' => 'failed', 'failed_deliveries' => 0]);

            return;
        }

        foreach ($jobs as $job) {
            $job->handle($tenants);
        }

        $campaign->fresh()->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
