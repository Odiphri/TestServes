<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_registration_does_not_provision_tenant_database(): void
    {
        $this->post(route('platform.register.submit'), [
            'name' => 'Oke Esther Etaredafe',
            'email' => 'kala@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'school_name' => 'CYOLE Stars Secondary School',
            'school_slug' => 'toke',
            'school_type' => 'Nursery',
            'expected_students_count' => '653',
            'legal_acceptance' => '1',
        ])->assertRedirect(route('platform.dashboard'));

        $school = School::where('slug', 'toke')->firstOrFail();

        $this->assertDatabaseHas('school_owners', [
            'school_id' => $school->id,
            'email' => 'kala@example.com',
        ]);
        $this->assertNull($school->expected_students_count);
        $this->assertNull($school->tenant_database_created_at);
        $this->assertDatabaseHas('legal_acceptances', [
            'acceptor_type' => SchoolOwner::class,
            'acceptor_id' => SchoolOwner::where('email', 'kala@example.com')->value('id'),
            'source' => 'owner_registration',
        ]);
        $this->assertAuthenticatedAs(SchoolOwner::where('email', 'kala@example.com')->first(), 'school_owner');
    }
}
