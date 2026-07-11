<?php

namespace Tests\Feature;

use App\Models\SchoolOwner;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPaymentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_page_renders_when_owner_has_no_school_yet(): void
    {
        $school = School::create([
            'name' => 'Archived School',
            'slug' => 'archived-school',
            'portal_url' => 'https://archived-school.testserves.com',
            'status' => 'suspended',
            'subscription_status' => 'cancelled',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Owner',
            'email' => 'owner-payments@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $school->delete();

        $this->actingAs($owner, 'school_owner')
            ->get(route('platform.payments'))
            ->assertOk()
            ->assertSee('No payments yet.');
    }
}
