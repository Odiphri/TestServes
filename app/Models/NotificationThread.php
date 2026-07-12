<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationThread extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'notification_recipient_id',
        'status',
        'closed_by_admin_id',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(NotificationRecipient::class, 'notification_recipient_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(NotificationMessage::class);
    }
}
