<?php

namespace App\Jobs;

use App\Models\NotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\User;
use App\Events\NotificationRecipientDelivered;
use App\Notifications\CampaignDatabaseNotification;
use App\Services\Notifications\NotificationTarget;
use App\Support\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeliverNotificationBatch
{
    public function __construct(public int $campaignId, public array $targets)
    {
    }

    public function handle(TenantDatabaseManager $tenants): void
    {
        $campaign = NotificationCampaign::on('mysql')->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $successful = 0;
        $failed = 0;

        foreach ($this->targets as $targetPayload) {
            $target = new NotificationTarget(
                $targetPayload['notifiable_type'],
                (int) $targetPayload['notifiable_id'],
                isset($targetPayload['school_id']) ? (int) $targetPayload['school_id'] : null
            );

            $recipient = NotificationRecipient::on('mysql')->firstOrCreate([
                'notification_campaign_id' => $campaign->id,
                'notifiable_type' => $target->notifiableType,
                'notifiable_id' => $target->notifiableId,
            ], [
                'school_id' => $target->schoolId,
            ]);

            try {
                $notifiable = $this->resolveNotifiable($target, $tenants);

                if (! $notifiable) {
                    throw new \RuntimeException('Recipient no longer exists.');
                }

                $notifiable->notify(new CampaignDatabaseNotification($campaign));

                DB::setDefaultConnection('mysql');
                $recipient->forceFill([
                    'delivered_at' => $recipient->delivered_at ?: now(),
                    'failed_at' => null,
                    'failure_reason' => null,
                ])->save();
                try {
                    broadcast(new NotificationRecipientDelivered($recipient->fresh('campaign')))->toOthers();
                } catch (Throwable) {
                    // Delivery is still valid; realtime is a best-effort layer.
                }
                $successful++;
            } catch (Throwable $exception) {
                DB::setDefaultConnection('mysql');
                $recipient->forceFill([
                    'failed_at' => now(),
                    'failure_reason' => $exception->getMessage(),
                ])->save();
                $failed++;
            }
        }

        NotificationCampaign::on('mysql')
            ->whereKey($campaign->id)
            ->update([
                'successful_deliveries' => DB::raw('successful_deliveries + '.$successful),
                'failed_deliveries' => DB::raw('failed_deliveries + '.$failed),
            ]);
    }

    private function resolveNotifiable(NotificationTarget $target, TenantDatabaseManager $tenants)
    {
        if ($target->notifiableType === SchoolOwner::class) {
            return SchoolOwner::on('mysql')->whereKey($target->notifiableId)->where('status', 'active')->first();
        }

        if ($target->notifiableType === User::class && $target->schoolId) {
            $school = School::on('mysql')->find($target->schoolId);
            if (! $school || ! $tenants->databaseExists($school)) {
                return null;
            }

            $tenants->activateExisting($school);

            return User::on('tenant')->whereKey($target->notifiableId)->where('is_active', true)->first();
        }

        return null;
    }
}
