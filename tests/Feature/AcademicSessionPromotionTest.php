<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcademicSessionPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_activation_auto_promotes_students_to_next_available_class(): void
    {
        $admin = $this->user('admin-session', 'admin');
        $jss1a = $this->schoolClass('JSS1A', 'JSS1', 'A');
        $jss2a = $this->schoolClass('JSS2A', 'JSS2', 'A');
        $ss3 = $this->schoolClass('SS3 Science', 'SS3', 'Science');

        $student = $this->student('student-promote', $jss1a);
        $graduatingStudent = $this->student('student-ss3', $ss3);

        $session = AcademicSession::create([
            'academic_year' => '2026/2027',
            'term' => 'First Term',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('academic-sessions.activate', $session))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'school_class_id' => $jss2a->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $graduatingStudent->id,
            'school_class_id' => $ss3->id,
        ]);
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $session->id,
            'is_active' => true,
            'promoted_students_count' => 1,
        ]);
    }

    public function test_non_admin_or_hod_cannot_mutate_academic_sessions(): void
    {
        $teacher = $this->user('teacher-session-blocked', 'teacher');

        $this->actingAs($teacher)
            ->post(route('academic-sessions.store'), [
                'academic_year' => '2026/2027',
                'term' => 'First Term',
            ])
            ->assertForbidden();
    }

    public function test_teacher_can_only_promote_or_demote_students_in_assigned_classes(): void
    {
        $teacher = $this->user('teacher-promotions', 'teacher');
        $jss1a = $this->schoolClass('JSS1A', 'JSS1', 'A');
        $jss2a = $this->schoolClass('JSS2A', 'JSS2', 'A');
        $jss3a = $this->schoolClass('JSS3A', 'JSS3', 'A');
        $outsideClass = $this->schoolClass('JSS2B', 'JSS2', 'B');

        $teacher->assignedClasses()->attach($jss2a->id);

        $assignedStudent = $this->student('student-assigned-promotion', $jss2a);
        $outsideStudent = $this->student('student-outside-promotion', $outsideClass);

        $this->actingAs($teacher)
            ->get(route('teacher.promotions'))
            ->assertOk()
            ->assertSee($assignedStudent->full_name)
            ->assertDontSee($outsideStudent->full_name);

        $this->actingAs($teacher)
            ->patch(route('teacher.promotions.demote', $assignedStudent))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $assignedStudent->id,
            'school_class_id' => $jss1a->id,
        ]);

        $outsideStudent->refresh();

        $this->actingAs($teacher)
            ->patch(route('teacher.promotions.promote', $outsideStudent))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $outsideStudent->id,
            'school_class_id' => $outsideClass->id,
        ]);
        $this->assertDatabaseHas('school_classes', ['id' => $jss3a->id]);
    }

    private function user(string $portalId, string $role): User
    {
        return User::create([
            'portal_id' => $portalId,
            'first_name' => ucfirst($role),
            'last_name' => 'User',
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function student(string $portalId, SchoolClass $class): User
    {
        return User::create([
            'portal_id' => $portalId,
            'first_name' => ucfirst(str_replace('-', ' ', $portalId)),
            'last_name' => 'Student',
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $class->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function schoolClass(string $name, string $level, ?string $stream): SchoolClass
    {
        return SchoolClass::create([
            'name' => $name,
            'level' => $level,
            'stream' => $stream,
            'is_active' => true,
        ]);
    }
}
