<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_user_management_hide_other_admins(): void
    {
        $currentAdmin = $this->admin('current-admin', 'Current Admin');
        $otherAdmin = $this->admin('other-admin', 'Other Admin');
        $teacher = User::create([
            'portal_id' => 'visible-teacher',
            'first_name' => 'Visible',
            'last_name' => 'Teacher',
            'email' => 'visible-teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($currentAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee($otherAdmin->full_name)
            ->assertSee($teacher->full_name);

        $this->actingAs($currentAdmin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertDontSee($otherAdmin->full_name)
            ->assertDontSee(route('admin.users.role.update', $currentAdmin))
            ->assertDontSee(route('admin.users.role.update', $otherAdmin))
            ->assertSee($teacher->full_name)
            ->assertDontSee('<option value="admin"', false);
    }

    public function test_admin_cannot_edit_an_admin_directly(): void
    {
        $currentAdmin = $this->admin('editor-admin', 'Editor Admin');
        $otherAdmin = $this->admin('protected-admin', 'Protected Admin');

        $this->actingAs($currentAdmin)
            ->put(route('admin.users.role.update', $otherAdmin), [
                'role' => 'teacher',
                'is_active' => '0',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function admin(string $portalId, string $name): User
    {
        [$firstName, $lastName] = explode(' ', $name, 2);

        return User::create([
            'portal_id' => $portalId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }
}
