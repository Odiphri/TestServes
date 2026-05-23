<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_authenticated_pages_render(): void
    {
        $admin = User::create([
            'portal_id' => 'admin-smoke',
            'first_name' => 'Admin',
            'last_name' => 'Smoke',
            'email' => 'admin-smoke@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        foreach ([
            '/admin/dashboard',
            '/admin/students',
            '/admin/staff',
            '/admin/classes',
            '/admin/subjects',
            '/admin/payments',
            '/admin/settings',
            '/admin/users',
            '/traffic',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_admin_can_delete_class(): void
    {
        $admin = User::create([
            'portal_id' => 'admin-delete-class',
            'first_name' => 'Admin',
            'last_name' => 'Delete',
            'email' => 'admin-delete-class@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $class = SchoolClass::create([
            'name' => 'JSS1A',
            'level' => 'JSS1',
            'stream' => 'A',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.classes.destroy', $class))
            ->assertRedirect();

        $this->assertDatabaseMissing('school_classes', ['id' => $class->id]);
    }

    public function test_admin_can_delete_a_whole_subject_group_for_one_section(): void
    {
        $admin = User::create([
            'portal_id' => 'admin-delete-subject-group',
            'first_name' => 'Admin',
            'last_name' => 'Subject',
            'email' => 'admin-delete-subject-group@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $jss1 = SchoolClass::create(['name' => 'JSS1A', 'level' => 'JSS1', 'stream' => 'A', 'is_active' => true]);
        $jss2 = SchoolClass::create(['name' => 'JSS2A', 'level' => 'JSS2', 'stream' => 'A', 'is_active' => true]);
        $ss1 = SchoolClass::create(['name' => 'SS1 Science', 'level' => 'SS1', 'stream' => 'Science', 'is_active' => true]);

        Subject::create(['name' => 'Mathematics', 'code' => 'MATH', 'school_class_id' => $jss1->id, 'is_active' => true]);
        Subject::create(['name' => 'Mathematics', 'code' => 'MATH', 'school_class_id' => $jss2->id, 'is_active' => true]);
        Subject::create(['name' => 'Mathematics', 'code' => 'MATH', 'school_class_id' => $ss1->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.subjects.group.destroy'), [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'section' => 'jss',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('subjects', ['name' => 'Mathematics', 'school_class_id' => $jss1->id]);
        $this->assertDatabaseMissing('subjects', ['name' => 'Mathematics', 'school_class_id' => $jss2->id]);
        $this->assertDatabaseHas('subjects', ['name' => 'Mathematics', 'school_class_id' => $ss1->id]);
    }
}
