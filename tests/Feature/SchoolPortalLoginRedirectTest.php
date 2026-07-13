<?php

namespace Tests\Feature;

use App\Models\User;
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
}
