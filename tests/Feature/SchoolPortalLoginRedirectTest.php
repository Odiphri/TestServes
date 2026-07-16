<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolPortalLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_school_portal_login_redirects_within_current_host(): void
    {
        $host = 'dasolad.'.config('testserves.root_domain');
        config(['app.url' => 'https://'.config('testserves.root_domain')]);
        $this->prepareSchoolPortal();

        User::query()->create([
            'portal_id' => 'admin-portal',
            'first_name' => 'Portal',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->withServerVariables([
            'HTTP_HOST' => $host,
            'HTTPS' => 'on',
        ])->post('https://'.$host.'/login', [
            'portal_id' => 'admin-portal',
            'password' => 'password123',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_teacher_password_change_redirect_stays_within_current_host(): void
    {
        $host = 'dasolad.'.config('testserves.root_domain');
        config(['app.url' => 'https://'.config('testserves.root_domain')]);
        $this->prepareSchoolPortal();

        User::query()->create([
            'portal_id' => 'teacher-portal',
            'first_name' => 'Portal',
            'last_name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $this->withServerVariables([
            'HTTP_HOST' => $host,
            'HTTPS' => 'on',
        ])->post('https://'.$host.'/login', [
            'portal_id' => 'teacher-portal',
            'password' => 'password123',
        ])->assertRedirect('/password/change');
    }

    private function prepareSchoolPortal(): void
    {
        School::create([
            'name' => 'Dasolad',
            'slug' => 'dasolad',
            'portal_url' => 'https://dasolad.'.config('testserves.root_domain'),
            'status' => 'active',
            'subscription_status' => 'active',
            'payment_status' => 'paid',
            'portal_locked' => false,
            'subscription_expires_at' => now()->addMonth()->toDateString(),
            'subscription_ends_at' => now()->addMonth(),
            'next_payment_due_at' => now()->addMonth()->toDateString(),
            'tenant_connection' => 'mysql',
            'tenant_database' => 'testserves_dasolad',
            'tenant_database_created_at' => now(),
        ]);

        $this->mock(TenantDatabaseManager::class, function ($mock): void {
            $mock->shouldReceive('databaseExists')->andReturnTrue();
            $mock->shouldReceive('activateExisting')->andReturnNull();
            $mock->shouldReceive('syncExistingTenant')->andReturnNull();
        });
    }
}
