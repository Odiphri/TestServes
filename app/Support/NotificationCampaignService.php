<?php

namespace App\Support;

use App\Models\NotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SchoolOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationCampaignService
{
    public function create(PlatformAdmin $admin, array $data): NotificationCampaign
    {
        PlatformPermission::require($admin, 'notifications.send');

        $isSystem = (bool) ($data['is_system_notification'] ?? false);
        $scope = $data['recipient_scope'];

        if ($isSystem) {
            PlatformPermission::require($admin, 'notifications.system');
            $data['allows_replies'] = false;
        }

        if (in_array($scope, ['all_school_owners', 'all_eligible_users'], true)) {
            PlatformPermission::require($admin, 'notifications.platform_wide');
        }

        $recipients = $this->resolveRecipients($admin, $scope, $data['recipient_payload'] ?? []);

        if ($recipients->isEmpty()) {
            throw ValidationException::withMessages(['recipient_scope' => 'No eligible recipients were found.']);
        }

        return DB::transaction(function () use ($admin, $data, $recipients, $isSystem) {
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
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'sent_at' => now(),
                'status' => 'sent',
                'recipient_count' => $recipients->count(),
            ]);

            $successful = 0;
            foreach ($recipients as $recipient) {
                NotificationRecipient::firstOrCreate([
                    'notification_campaign_id' => $campaign->id,
                    'notifiable_type' => $recipient::class,
                    'notifiable_id' => $recipient->getKey(),
                ], [
                    'school_id' => $recipient instanceof SchoolOwner ? $recipient->school_id : ($data['school_id'] ?? null),
                    'delivered_at' => now(),
                ]);
                $successful++;
            }

            $campaign->update(['successful_deliveries' => $successful]);

            PlatformActivity::log('notification_campaign_created', "Created notification campaign {$campaign->title}.", $campaign, [
                'school_id' => $campaign->school_id,
                'new_values' => [
                    'recipient_scope' => $campaign->recipient_scope,
                    'recipient_count' => $campaign->recipient_count,
                    'is_system_notification' => $campaign->is_system_notification,
                ],
            ]);

            return $campaign->fresh('recipients');
        });
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

    private function resolveRecipients(PlatformAdmin $admin, string $scope, array $payload): Collection
    {
        return match ($scope) {
            'single_school_owner' => SchoolOwner::query()
                ->whereKey($payload['school_owner_id'] ?? null)
                ->where('status', 'active')
                ->get(),
            'selected_school_owners' => SchoolOwner::query()
                ->whereIn('id', array_unique($payload['school_owner_ids'] ?? []))
                ->where('status', 'active')
                ->get(),
            'school_owners_for_school' => SchoolOwner::query()
                ->where('school_id', $payload['school_id'] ?? null)
                ->where('status', 'active')
                ->get(),
            'all_school_owners' => SchoolOwner::query()
                ->where('status', 'active')
                ->get(),
            default => collect(),
        };
    }
}
