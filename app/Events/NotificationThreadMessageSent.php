<?php

namespace App\Events;

use App\Models\NotificationMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationThreadMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public NotificationMessage $message)
    {
        $this->message->loadMissing('thread.recipient');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('notification-thread.'.$this->message->notification_thread_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'thread_id' => $this->message->notification_thread_id,
            'sender_type' => $this->message->sender_type,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name ?? $this->message->sender?->full_name ?? 'Support',
            'message' => $this->message->message,
            'created_at' => optional($this->message->created_at)->toISOString(),
        ];
    }
}
