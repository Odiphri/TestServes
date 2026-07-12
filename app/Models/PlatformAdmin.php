<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\PublicDiskUrl;
use App\Support\PlatformPermission;

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

        return in_array($section, PlatformPermission::sectionsForRole($this->role), true);
    }

    public static function rolePermissions(): array
    {
        return [
            'sales_admin' => PlatformPermission::sectionsForRole('sales_admin'),
            'support_admin' => PlatformPermission::sectionsForRole('support_admin'),
            'finance_admin' => PlatformPermission::sectionsForRole('finance_admin'),
            'operations_admin' => PlatformPermission::sectionsForRole('operations_admin'),
        ];
    }

    public static function roles(): array
    {
        return ['super_admin', 'sales_admin', 'finance_admin', 'support_admin', 'operations_admin'];
    }

    public function canPerform(string $action): bool
    {
        return PlatformPermission::allows($this, $action);
    }

    public function roleLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->role));
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        return PublicDiskUrl::make($this->profile_picture);
    }
}
