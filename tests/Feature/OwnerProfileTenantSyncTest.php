<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolOwner;
use App\Support\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerProfileTenantSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_profile_update_syncs_active_tenant_admin_login(): void
    {
        $school = School::create([
            'name' => 'CYOLE Stars',
            'slug' => 'cyole-stars',
            'portal_url' => 'https://cyole-stars.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Old Owner',
            'email' => 'old-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->mock(TenantDatabaseManager::class)
            ->shouldReceive('createAndMigrate')
            ->once()
            ->withArgs(fn (School $givenSchool) => $givenSchool->is($school));

        $this->actingAs($owner, 'school_owner')
            ->put(route('platform.profile.update'), [
                'name' => 'New Owner',
                'email' => 'new-owner@example.com',
                'phone' => '08030000000',
            ])
            ->assertRedirect();
    }
}
