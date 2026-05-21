<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\SchoolClass;
use App\Models\User;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_authenticated_pages_render(): void
    {
        $admin = User::create([
            'portal_id' => 'admin-smoke',
            'first_name' => 'Admin',
            'last_name' => 'Smoke',
            'email' => 'admin-smoke@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        foreach ([
            '/admin/dashboard',
            '/admin/students',
            '/admin/staff',
            '/admin/classes',
            '/admin/subjects',
            '/admin/payments',
            '/admin/settings',
            '/admin/users',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_admin_can_delete_class(): void
    {
        $admin = User::create([
            'portal_id' => 'admin-delete-class',
            'first_name' => 'Admin',
            'last_name' => 'Delete',
            'email' => 'admin-delete-class@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $class = SchoolClass::create([
            'name' => 'JSS1A',
            'level' => 'JSS1',
            'stream' => 'A',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.classes.destroy', $class))
            ->assertRedirect();

        $this->assertDatabaseMissing('school_classes', ['id' => $class->id]);
    }
}
