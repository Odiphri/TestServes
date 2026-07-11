<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_delete_school_owner_account(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Cleanup School',
            'slug' => 'cleanup-school',
            'portal_url' => 'https://cleanup-school.testserves.com',
            'status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Cleanup Owner',
            'email' => 'cleanup-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.school-owners.destroy', $owner))
            ->assertRedirect(route('super-admin.school-owners.index'));

        $this->assertDatabaseMissing('school_owners', ['id' => $owner->id]);
        $this->assertDatabaseHas('schools', ['id' => $school->id]);
    }

    public function test_super_admin_can_search_platform_admin_users(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-search-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        PlatformAdmin::create([
            'name' => 'Finance Search Target',
            'email' => 'finance-target@example.com',
            'password' => 'password123',
            'role' => 'finance_admin',
            'is_active' => true,
        ]);

        PlatformAdmin::create([
            'name' => 'Support Hidden',
            'email' => 'support-hidden@example.com',
            'password' => 'password123',
            'role' => 'support_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->get(route('super-admin.admin-users.index', ['search' => 'finance-target']))
            ->assertOk()
            ->assertSee('Finance Search Target')
            ->assertDontSee('Support Hidden');
    }

    public function test_super_admin_can_delete_platform_admin_user(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-delete-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $target = PlatformAdmin::create([
            'name' => 'Delete Platform User',
            'email' => 'delete-platform-user@example.com',
            'password' => 'password123',
            'role' => 'support_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.admin-users.destroy', $target))
            ->assertRedirect();

        $this->assertSoftDeleted('platform_admins', ['id' => $target->id]);
    }

    public function test_school_admin_can_delete_tenant_user(): void
    {
        $admin = User::create([
            'portal_id' => 'cleanup-admin',
            'first_name' => 'Cleanup',
            'last_name' => 'Admin',
            'email' => 'cleanup-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $student = User::create([
            'portal_id' => 'cleanup-student',
            'first_name' => 'Cleanup',
            'last_name' => 'Student',
            'email' => 'cleanup-student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }
}
