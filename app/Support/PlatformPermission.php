<?php

namespace App\Support;

use App\Models\PlatformAdmin;

class PlatformPermission
{
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

    public static function allows(?PlatformAdmin $admin, string $action): bool
    {
        if (! $admin || ! $admin->is_active) {
            return false;
        }

        if ($admin->isSuperAdmin()) {
            return true;
        }

        return in_array($action, self::permissionsForRole($admin->role), true);
    }

    public static function require(?PlatformAdmin $admin, string $action): void
    {
        abort_unless(self::allows($admin, $action), 403);
    }

    public static function permissionsForRole(string $role): array
    {
        return match ($role) {
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
                'notifications.send',
            ],
            'operations_admin' => [
                'schools.view',
                'school_owners.view',
                'plans.view',
                'operations.view',
                'operations.manage',
                'schools.trials.request',
                'notifications.send',
            ],
            default => [],
        };
    }

    public static function sectionsForRole(string $role): array
    {
        if ($role === 'super_admin') {
            return [
                'dashboard',
                'schools',
                'school_owners',
                'subscription_plans',
                'payments',
                'payment_disputes',
                'support_tickets',
                'live_support',
                'activity_logs',
                'system_settings',
                'admin_users',
            ];
        }

        return match ($role) {
            'sales_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans'],
            'finance_admin' => ['dashboard', 'schools', 'subscription_plans', 'payments', 'payment_disputes'],
            'support_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'support_tickets', 'live_support'],
            'operations_admin' => ['dashboard', 'schools', 'school_owners', 'subscription_plans', 'support_tickets'],
            default => [],
        };
    }
}
