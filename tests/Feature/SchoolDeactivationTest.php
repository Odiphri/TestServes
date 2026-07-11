<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_school_does_not_soft_delete_it(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'CYOLE Stars',
            'slug' => 'cyole',
            'portal_url' => 'https://cyole.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'deactivated']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('deactivated', $school->status);
        $this->assertSame('cancelled', $school->subscription_status);
        $this->assertNull($school->deleted_at);
        $this->assertNotNull($school->deactivated_at);
        $this->assertNotNull($school->delete_scheduled_at);
    }

    public function test_delete_school_soft_deletes_for_archived_restore(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'delete-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me',
            'portal_url' => 'https://delete-me.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.schools.destroy', $school))
            ->assertRedirect(route('super-admin.schools.index'));

        $this->assertSoftDeleted('schools', ['id' => $school->id]);
    }
}
