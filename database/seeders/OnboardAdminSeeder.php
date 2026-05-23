<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OnboardAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['portal_id' => 'Onboard'],
            [
                'first_name' => 'Odiphri Marvelous',
                'last_name' => 'OgheneKaro',
                'email' => null,
                'password' => Hash::make('@elvira06'),
                'role' => 'admin',
                'must_change_password' => false,
                'is_active' => true,
                'password_changed_at' => now(),
            ]
        );

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');
        Profile::firstOrCreate(['user_id' => $admin->id]);
    }
}
