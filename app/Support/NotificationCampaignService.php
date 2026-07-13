<?php

namespace App\Support;

use App\Models\NotificationCampaign;
use App\Jobs\DispatchNotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Services\Notifications\RecipientResolver;
use App\Support\TenantDatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationCampaignService
{
    public function __construct(private readonly RecipientResolver $recipients)
    {
    }

    public function create(PlatformAdmin $admin, array $data): NotificationCampaign
    {
        abort_unless($admin->canPerform('notifications.send'), 403);

        $isSystem = (bool) ($data['is_system_notification'] ?? false);
        $scope = $data['recipient_scope'];

        if ($isSystem) {
            abort_unless($admin->canPerform('notifications.system'), 403);
            $data['allows_replies'] = false;
        }

        if (in_array($scope, ['all_school_owners', 'all_eligible_users', 'all_users'], true)) {
            abort_unless($admin->canPerform('notifications.platform_wide'), 403);
        }

        $recipientCount = $this->recipients->count($admin, $scope, array_merge($data['recipient_payload'] ?? [], [
            'is_system_notification' => $isSystem,
        ]));

        if ($recipientCount < 1) {
            throw ValidationException::withMessages(['recipient_scope' => 'No eligible recipients were found.']);
        }

        $scheduledAt = isset($data['scheduled_at']) && $data['scheduled_at'] ? \Illuminate\Support\Carbon::parse($data['scheduled_at']) : null;
        $shouldSchedule = $scheduledAt && $scheduledAt->isFuture();

        $campaign = DB::transaction(function () use ($admin, $data, $recipientCount, $isSystem, $scheduledAt, $shouldSchedule) {
            $campaign = NotificationCampaign::create([
                'created_by_admin_id' => $admin->id,
                'created_by_role' => $admin->role,
                'school_id' => $data['school_id'] ?? null,
                'type' => $data['type'] ?? 'general',
                'title' => $data['title'],
                'body' => $data['body'],
                'recipient_scope' => $data['recipient_scope'],
                'recipient_payload' => $data['recipient_payload'] ?? null,
                'action_url' => $data['action_url'] ?? null,
                'is_system_notification' => $isSystem,
                'allows_replies' => ! $isSystem && (bool) ($data['allows_replies'] ?? true),
                'expires_at' => $data['expires_at'] ?? null,
                'scheduled_at' => $scheduledAt,
                'status' => $shouldSchedule ? 'scheduled' : 'queued',
                'recipient_count' => $recipientCount,
            ]);

            PlatformActivity::log('notification_campaign_created', "Created notification campaign {$campaign->title}.", $campaign, [
                'school_id' => $campaign->school_id,
                'new_values' => [
                    'recipient_scope' => $campaign->recipient_scope,
                    'recipient_count' => $campaign->recipient_count,
                    'is_system_notification' => $campaign->is_system_notification,
                ],
            ]);

            return $campaign;
        });

        if (! $shouldSchedule) {
            (new DispatchNotificationCampaign($campaign->id))->handle($this->recipients, app(TenantDatabaseManager::class));
        }

        return $campaign->fresh('recipients');
    }

    public function sendWelcome(Model $recipient, string $title, string $body, ?School $school = null): ?NotificationCampaign
    {
        $existing = NotificationRecipient::query()
            ->where('notifiable_type', $recipient::class)
            ->where('notifiable_id', $recipient->getKey())
            ->whereHas('campaign', fn ($query) => $query->where('type', 'welcome'))
            ->exists();

        if ($existing) {
            return null;
        }

        return DB::transaction(function () use ($recipient, $title, $body, $school) {
            $campaign = NotificationCampaign::create([
                'school_id' => $school?->id,
                'type' => 'welcome',
                'title' => $title,
                'body' => $body,
                'recipient_scope' => 'single_user',
                'is_system_notification' => true,
                'allows_replies' => false,
                'sent_at' => now(),
                'status' => 'sent',
                'recipient_count' => 1,
                'successful_deliveries' => 1,
            ]);

            NotificationRecipient::create([
                'notification_campaign_id' => $campaign->id,
                'notifiable_type' => $recipient::class,
                'notifiable_id' => $recipient->getKey(),
                'school_id' => $school?->id,
                'delivered_at' => now(),
            ]);

            return $campaign;
        });
    }

}
