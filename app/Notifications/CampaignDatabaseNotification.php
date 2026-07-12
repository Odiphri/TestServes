<?php

namespace App\Notifications;

use App\Models\NotificationCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CampaignDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly NotificationCampaign $campaign)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'body' => $this->campaign->body,
            'type' => $this->campaign->type,
            'action_url' => $this->campaign->action_url,
            'is_system_notification' => $this->campaign->is_system_notification,
            'allows_replies' => $this->campaign->allows_replies,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable) + [
            'created_at' => now()->toISOString(),
        ]);
    }
}
