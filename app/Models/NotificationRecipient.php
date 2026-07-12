<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_campaign_id',
        'notifiable_type',
        'notifiable_id',
        'school_id',
        'delivered_at',
        'read_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NotificationCampaign::class, 'notification_campaign_id');
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function thread(): HasOne
    {
        return $this->hasOne(NotificationThread::class);
    }
}
