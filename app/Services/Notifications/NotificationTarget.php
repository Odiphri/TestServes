<?php

namespace App\Services\Notifications;

class NotificationTarget
{
    public function __construct(
        public readonly string $notifiableType,
        public readonly int $notifiableId,
        public readonly ?int $schoolId = null,
    ) {
    }

    public function key(): string
    {
        return $this->notifiableType.':'.$this->notifiableId.':'.($this->schoolId ?? 'central');
    }

    public function toArray(): array
    {
        return [
            'notifiable_type' => $this->notifiableType,
            'notifiable_id' => $this->notifiableId,
            'school_id' => $this->schoolId,
        ];
    }
}
