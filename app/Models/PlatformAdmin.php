<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\PublicDiskUrl;
use App\Support\PlatformAdminAccess;
use Spatie\Permission\Traits\HasRoles;

class PlatformAdmin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = PlatformAdminAccess::GUARD;

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
        return $this->hasRole('super_admin') || $this->role === 'super_admin';
    }

    public function canAccessPlatformSection(string $section): bool
    {
        if ($section === 'dashboard' || $this->isSuperAdmin()) {
            return true;
        }

        $permission = PlatformAdminAccess::permissionForSection($section);

        return $permission ? $this->can($permission) : false;
    }

    public static function rolePermissions(): array
    {
        return [
            'sales_admin' => PlatformAdminAccess::sectionsForRole('sales_admin'),
            'support_admin' => PlatformAdminAccess::sectionsForRole('support_admin'),
            'finance_admin' => PlatformAdminAccess::sectionsForRole('finance_admin'),
            'operations_admin' => PlatformAdminAccess::sectionsForRole('operations_admin'),
        ];
    }

    public function canPerform(string $action): bool
    {
        return $this->isSuperAdmin() || $this->can($action);
    }

    public function roleLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->role));
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        return PublicDiskUrl::make($this->profile_picture);
    }

    protected static function booted(): void
    {
        static::saved(function (PlatformAdmin $admin) {
            PlatformAdminAccess::syncAdminRole($admin);
        });
    }
}
