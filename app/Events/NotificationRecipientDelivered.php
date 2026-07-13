<?php

namespace App\Events;

use App\Models\NotificationRecipient;
use App\Models\SchoolOwner;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationRecipientDelivered implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public NotificationRecipient $recipient)
    {
        $this->recipient->loadMissing('campaign');
    }

    public function broadcastOn(): array
    {
        if ($this->recipient->notifiable_type !== SchoolOwner::class) {
            return [];
        }

        return [
            new Channel('owner-notifications.'.$this->recipient->notifiable_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.delivered';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->recipient->id,
            'title' => $this->recipient->campaign?->title ?? 'Notification',
            'body' => $this->recipient->campaign?->body ?? '',
            'type' => $this->recipient->campaign?->type ?? 'general',
            'is_system_notification' => (bool) $this->recipient->campaign?->is_system_notification,
            'allows_replies' => (bool) $this->recipient->campaign?->allows_replies,
            'delivered_at' => optional($this->recipient->delivered_at)->toISOString(),
        ];
    }
}
