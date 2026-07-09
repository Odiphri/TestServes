<?php

namespace Database\Seeders;

use App\Models\PlatformAdmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        PlatformAdmin::updateOrCreate(
            ['email' => 'odiphrimarvellous@gmail.com'],
            [
                'name' => 'Odiphri Marvelous',
                'password' => Hash::make('elvira06'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
