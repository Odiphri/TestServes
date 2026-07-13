<?php

namespace App\Events;

use App\Models\LiveSupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public LiveSupportMessage $message)
    {
        $this->message->loadMissing('conversation.school');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('live-support.'.$this->message->live_support_conversation_id),
            new Channel('live-support-token.'.$this->message->conversation?->access_token),
            new Channel('live-support-admin'),
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
            'conversation_id' => $this->message->live_support_conversation_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->sender_name,
            'message' => $this->message->message,
            'created_at' => optional($this->message->created_at)->toISOString(),
            'conversation' => [
                'id' => $this->message->conversation?->id,
                'reference' => $this->message->conversation?->reference,
                'subject' => $this->message->conversation?->subject,
                'visitor_name' => $this->message->conversation?->visitor_name,
                'visitor_email' => $this->message->conversation?->visitor_email,
                'school_name' => $this->message->conversation?->school?->name,
                'status' => $this->message->conversation?->status,
                'last_message_at' => optional($this->message->conversation?->last_message_at)->toISOString(),
            ],
        ];
    }
}
