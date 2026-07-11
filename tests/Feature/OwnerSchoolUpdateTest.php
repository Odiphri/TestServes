<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerSchoolUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_school_with_plain_post(): void
    {
        $school = School::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'portal_url' => 'https://old-name.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Owner',
            'email' => 'owner-school@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->post(route('platform.school.update'), [
                'school_name' => 'New Name',
                'school_slug' => 'new-name',
                'school_type' => 'Secondary',
                'expected_students_count' => 300,
                'contact_email' => 'school@example.com',
                'contact_phone' => '08030000000',
                'school_address' => 'Test address',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'school_type' => 'Secondary',
            'expected_students_count' => 300,
        ]);
    }
}
