<?php

namespace Tests\Feature;

use App\Models\Override;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OverrideCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_override_when_student_id_and_name_match(): void
    {
        $admin = $this->user('admin', 'admin-override');
        $class = SchoolClass::create([
            'name' => 'JSS1 General',
            'level' => 'JSS1',
            'stream' => 'General',
            'is_active' => true,
        ]);
        $student = $this->user('student', 'stu-override', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'school_class_id' => $class->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.overrides.store'), [
                'student_portal_id' => $student->portal_id,
                'student_name' => 'Jane Doe',
                'reason' => 'Cleared by office',
                'expiry_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('overrides', [
            'student_id' => $student->id,
            'approved_by' => $admin->id,
            'reason' => 'Cleared by office',
            'is_active' => true,
        ]);
    }

    public function test_override_is_not_created_when_student_id_and_name_do_not_match(): void
    {
        $hod = $this->user('hod', 'hod-override');
        $student = $this->user('student', 'stu-no-match', [
            'first_name' => 'Ade',
            'last_name' => 'Cole',
        ]);

        $this->actingAs($hod)
            ->from(route('hod.overrides'))
            ->post(route('hod.overrides.store'), [
                'student_portal_id' => $student->portal_id,
                'student_name' => 'Wrong Name',
                'reason' => 'Testing mismatch',
                'expiry_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('hod.overrides'))
            ->assertSessionHasErrors('student_portal_id');

        $this->assertSame(0, Override::count());
    }

    public function test_override_can_be_deleted(): void
    {
        $hod = $this->user('hod', 'hod-delete-override');
        $student = $this->user('student', 'stu-delete-override');
        $override = Override::create([
            'student_id' => $student->id,
            'approved_by' => $hod->id,
            'reason' => 'Temporary access',
            'expiry_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($hod)
            ->delete(route('hod.overrides.destroy', $override))
            ->assertRedirect();

        $this->assertDatabaseMissing('overrides', ['id' => $override->id]);
    }

    private function user(string $role, string $portalId, array $overrides = []): User
    {
        return User::create(array_merge([
            'portal_id' => $portalId,
            'first_name' => ucfirst($role),
            'last_name' => 'User',
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ], $overrides));
    }
}
