<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DemoRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_owner_id',
        'school_id',
        'school_name',
        'contact_person',
        'email',
        'phone',
        'location',
        'message',
        'preferred_demo_date',
        'status',
        'assigned_admin_id',
        'notes',
        'demo_token',
        'demo_access_token',
        'approved_at',
        'demo_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_demo_date' => 'datetime',
            'approved_at' => 'datetime',
            'demo_expires_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SchoolOwner::class, 'school_owner_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }

    public function approveForDemoAccess(int $days = 7): void
    {
        $this->forceFill([
            'status' => 'approved',
            'approved_at' => $this->approved_at ?: now(),
            'demo_expires_at' => now()->addDays($days),
            'demo_token' => $this->demo_token ?: Str::random(18),
            'demo_access_token' => $this->demo_access_token ?: Str::random(32),
        ])->save();
    }

    public function isDemoAccessActive(): bool
    {
        return $this->status === 'approved'
            && filled($this->demo_token)
            && filled($this->demo_access_token)
            && $this->demo_expires_at
            && $this->demo_expires_at->isFuture();
    }

    public function getDemoUrlAttribute(): ?string
    {
        if (! $this->isDemoAccessActive()) {
            return null;
        }

        return route('demo-cbt.login', [
            'demoRequest' => $this->demo_token,
            'expires' => $this->demo_expires_at->format('YmdHis'),
            'accessToken' => $this->demo_access_token,
        ]);
    }
}
