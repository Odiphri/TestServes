<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_thread_id',
        'sender_type',
        'sender_id',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(NotificationThread::class, 'notification_thread_id');
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }
}
