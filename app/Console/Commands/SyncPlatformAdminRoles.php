<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use App\Support\PlatformAdminAccess;
use Illuminate\Console\Command;

class SyncPlatformAdminRoles extends Command
{
    protected $signature = 'platform:sync-admin-roles {--dry-run : Show mapping without writing roles}';

    protected $description = 'Seed Spatie platform admin roles and map existing platform_admins.role values to Spatie roles.';

    public function handle(): int
    {
        $admins = PlatformAdmin::query()->orderBy('id')->get();

        $this->info('Platform admin role mapping plan:');
        foreach ($admins as $admin) {
            $mappedRole = in_array($admin->role, PlatformAdminAccess::roles(), true) ? $admin->role : 'support_admin';
            $note = $mappedRole === $admin->role ? '' : ' (fallback from unknown role)';
            $this->line(" - {$admin->email}: {$admin->role} -> {$mappedRole}{$note}");
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run only. No roles were written.');

            return self::SUCCESS;
        }

        PlatformAdminAccess::seedRolesAndPermissions();

        foreach ($admins as $admin) {
            PlatformAdminAccess::syncAdminRole($admin);
        }

        $this->info('Platform admin Spatie roles are synced.');

        return self::SUCCESS;
    }
}
