<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PlatformAdmin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'profile_picture',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canAccessPlatformSection(string $section): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($section, self::rolePermissions()[$this->role] ?? [], true);
    }

    public static function rolePermissions(): array
    {
        return [
            'sales_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'demo_requests', 'payments'],
            'support_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'support_tickets', 'live_support'],
            'finance_admin' => ['dashboard', 'schools', 'payments', 'payment_disputes'],
        ];
    }

    public static function roles(): array
    {
        return ['super_admin', 'sales_admin', 'support_admin', 'finance_admin'];
    }

    public function roleLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->role));
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if (blank($this->profile_picture)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($this->profile_picture)) {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($this->profile_picture, '/'));

        return '/storage/'.$path.'?v='.$disk->lastModified($this->profile_picture);
    }
}
