<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'user_name',
        'role',
        'ip_address',
        'device_type',
        'browser',
        'user_agent',
        'pages_visited',
        'login_at',
        'last_activity_at',
        'logout_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'pages_visited' => 'array',
            'login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
