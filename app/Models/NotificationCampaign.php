<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_admin_id',
        'created_by_role',
        'school_id',
        'type',
        'title',
        'body',
        'recipient_scope',
        'recipient_payload',
        'action_url',
        'is_system_notification',
        'allows_replies',
        'expires_at',
        'scheduled_at',
        'sent_at',
        'status',
        'recipient_count',
        'successful_deliveries',
        'failed_deliveries',
    ];

    protected function casts(): array
    {
        return [
            'recipient_payload' => 'array',
            'is_system_notification' => 'boolean',
            'allows_replies' => 'boolean',
            'expires_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'created_by_admin_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }
}
