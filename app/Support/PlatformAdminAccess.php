<?php

namespace App\Support;

use App\Models\PlatformAdmin;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PlatformAdminAccess
{
    public const GUARD = 'platform_admin';

    public const ACTIONS = [
        'schools.view',
        'schools.create',
        'schools.update',
        'schools.delete',
        'schools.lifecycle',
        'schools.trials.request',
        'schools.trials.manage',
        'schools.tenant.manage',
        'school_owners.view',
        'school_owners.update',
        'school_owners.delete',
        'school_owners.reset_password',
        'plans.view',
        'plans.manage',
        'payments.view',
        'payments.manage',
        'payments.approve',
        'payments.refund',
        'payment_disputes.view',
        'payment_disputes.manage',
        'support.view',
        'support.manage',
        'support.assign',
        'contact_inquiries.view',
        'contact_inquiries.manage',
        'support.impersonate',
        'operations.view',
        'operations.manage',
        'notifications.send',
        'notifications.system',
        'notifications.platform_wide',
        'admin_users.manage',
        'settings.manage',
        'secrets.manage',
        'activity_logs.view',
        'impersonation.manage',
    ];

    public static function roles(): array
    {
        return ['super_admin', 'sales_admin', 'finance_admin', 'support_admin', 'operations_admin'];
    }

    public static function permissionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => self::ACTIONS,
            'sales_admin' => [
                'schools.view',
                'school_owners.view',
                'plans.view',
                'schools.trials.request',
                'notifications.send',
            ],
            'finance_admin' => [
                'schools.view',
                'plans.view',
                'payments.view',
                'payments.manage',
                'payments.approve',
                'payments.refund',
                'payment_disputes.view',
                'payment_disputes.manage',
                'notifications.send',
            ],
            'support_admin' => [
                'schools.view',
                'school_owners.view',
                'school_owners.reset_password',
                'plans.view',
                'support.view',
                'support.manage',
                'support.assign',
                'contact_inquiries.view',
                'contact_inquiries.manage',
                'notifications.send',
            ],
            'operations_admin' => [
                'schools.view',
                'school_owners.view',
                'plans.view',
                'operations.view',
                'operations.manage',
                'contact_inquiries.view',
                'schools.trials.request',
                'notifications.send',
            ],
            default => [],
        };
    }

    public static function sectionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                'dashboard',
                'schools',
                'school_owners',
                'subscription_plans',
                'payments',
                'payment_disputes',
                'support_tickets',
                'contact_inquiries',
                'live_support',
                'notifications',
                'activity_logs',
                'system_settings',
                'admin_users',
            ],
            'sales_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'notifications'],
            'finance_admin' => ['dashboard', 'schools', 'subscription_plans', 'payments', 'payment_disputes', 'notifications'],
            'support_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'support_tickets', 'contact_inquiries', 'live_support', 'notifications'],
            'operations_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'support_tickets', 'contact_inquiries', 'notifications'],
            default => [],
        };
    }

    public static function permissionForSection(string $section): ?string
    {
        return match ($section) {
            'dashboard' => null,
            'schools' => 'schools.view',
            'school_owners' => 'school_owners.view',
            'subscription_plans' => 'plans.view',
            'payments' => 'payments.view',
            'payment_disputes' => 'payment_disputes.view',
            'support_tickets', 'live_support' => 'support.view',
            'contact_inquiries' => 'contact_inquiries.view',
            'notifications' => 'notifications.send',
            'activity_logs' => 'activity_logs.view',
            'system_settings' => 'settings.manage',
            'admin_users' => 'admin_users.manage',
            default => null,
        };
    }

    public static function seedRolesAndPermissions(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        foreach (self::ACTIONS as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        foreach (self::roles() as $roleName) {
            $role = Role::findOrCreate($roleName, self::GUARD);
            $role->syncPermissions(self::permissionsForRole($roleName));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function syncAdminRole(PlatformAdmin $admin): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return;
        }

        self::seedRolesAndPermissions();

        $role = in_array($admin->role, self::roles(), true) ? $admin->role : 'support_admin';
        $admin->syncRoles([$role]);
    }
}
