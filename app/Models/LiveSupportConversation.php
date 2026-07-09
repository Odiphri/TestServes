<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveSupportConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_owner_id',
        'assigned_admin_id',
        'reference',
        'access_token',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'subject',
        'status',
        'priority',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LiveSupportMessage::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SchoolOwner::class, 'school_owner_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }
}
