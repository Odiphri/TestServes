<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerBrandingUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_branding_with_plain_post(): void
    {
        $school = School::create([
            'name' => 'CYOLE Stars',
            'slug' => 'cyole',
            'portal_url' => 'https://cyole.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Oke Esther',
            'email' => 'owner-branding@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->post(route('platform.branding.update'), [
                'portal_display_name' => 'CYOLE Portal',
                'short_name' => 'CYOLE',
                'primary_color' => '#123456',
                'secondary_color' => '#234567',
                'accent_color' => '#345678',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_branding_settings', [
            'school_id' => $school->id,
            'portal_display_name' => 'CYOLE Portal',
            'short_name' => 'CYOLE',
            'primary_color' => '#123456',
            'secondary_color' => '#234567',
            'accent_color' => '#345678',
        ]);
    }

    public function test_owner_can_save_branding_from_direct_branding_url(): void
    {
        $school = School::create([
            'name' => 'Direct Stars',
            'slug' => 'direct-stars',
            'portal_url' => 'https://direct-stars.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Direct Owner',
            'email' => 'direct-branding@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->post('/branding', [
                'portal_display_name' => 'Direct Portal',
                'primary_color' => '#123456',
                'secondary_color' => '#234567',
                'accent_color' => '#345678',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_branding_settings', [
            'school_id' => $school->id,
            'portal_display_name' => 'Direct Portal',
        ]);
    }
}
