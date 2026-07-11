<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\SubscriptionPlan;
use App\Support\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerTrialActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_start_trial_and_provisioning_is_called(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'trial_days' => 7,
            'features' => ['Admin dashboard'],
            'status' => 'active',
        ]);

        $school = School::create([
            'subscription_plan_id' => $plan->id,
            'name' => 'CYOLE Stars',
            'slug' => 'cyole',
            'portal_url' => 'https://cyole.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Oke Esther',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->mock(TenantDatabaseManager::class)
            ->shouldReceive('createAndMigrate')
            ->once()
            ->withArgs(fn (School $givenSchool) => $givenSchool->is($school));

        $this->actingAs($owner, 'school_owner')
            ->post(route('platform.trial.start'))
            ->assertRedirect(route('platform.dashboard'));

        $school->refresh();

        $this->assertSame('trial', $school->status);
        $this->assertSame('trial', $school->subscription_status);
        $this->assertTrue($school->hasPortalAccess());
    }
}
