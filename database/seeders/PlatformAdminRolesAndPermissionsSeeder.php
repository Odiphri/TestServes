<?php

namespace Database\Seeders;

use App\Models\PlatformAdmin;
use App\Support\PlatformAdminAccess;
use Illuminate\Database\Seeder;

class PlatformAdminRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        PlatformAdminAccess::seedRolesAndPermissions();

        PlatformAdmin::query()->each(function (PlatformAdmin $admin) {
            PlatformAdminAccess::syncAdminRole($admin);
        });
    }
}
