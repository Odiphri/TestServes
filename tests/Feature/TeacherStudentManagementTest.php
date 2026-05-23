<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\StudentRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_assigned_to_classes_can_view_only_students_in_those_classes(): void
    {
        $teacher = $this->teacher('teacher-students-view');
        [$firstClass, $secondClass, $outsideClass] = $this->classes();

        $teacher->assignedClasses()->attach([$firstClass->id, $secondClass->id]);

        $firstStudent = $this->student('student-in-first', $firstClass);
        $secondStudent = $this->student('student-in-second', $secondClass);
        $outsideStudent = $this->student('student-outside', $outsideClass);

        $response = $this->actingAs($teacher)->get(route('teacher.students'));

        $response->assertOk();
        $response->assertSee($firstStudent->full_name);
        $response->assertSee($secondStudent->full_name);
        $response->assertDontSee($outsideStudent->full_name);
    }

    public function test_teacher_with_one_assigned_class_can_create_student_without_selecting_class(): void
    {
        $teacher = $this->teacher('teacher-single-class-create');
        [$assignedClass] = $this->classes();
        $studentRole = StudentRole::create(['name' => 'Regular Student', 'is_active' => true]);

        $teacher->assignedClasses()->attach($assignedClass->id);

        $this->actingAs($teacher)
            ->post(route('teacher.students.store'), $this->studentPayload([
                'portal_id' => 'AUTO-CLASS-001',
                'student_role_id' => $studentRole->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'portal_id' => 'AUTO-CLASS-001',
            'role' => 'student',
            'school_class_id' => $assignedClass->id,
        ]);
    }

    public function test_class_teacher_can_access_students_for_their_class(): void
    {
        $teacher = $this->teacher('teacher-class-teacher');
        [$assignedClass, $outsideClass] = $this->classes();
        $assignedClass->update(['class_teacher_id' => $teacher->id]);

        $assignedStudent = $this->student('student-for-class-teacher', $assignedClass);
        $outsideStudent = $this->student('student-not-for-class-teacher', $outsideClass);

        $response = $this->actingAs($teacher)->get(route('teacher.students'));

        $response->assertOk();
        $response->assertSee($assignedStudent->full_name);
        $response->assertDontSee($outsideStudent->full_name);
    }

    public function test_teacher_with_multiple_assigned_classes_can_create_student_in_selected_assigned_class(): void
    {
        $teacher = $this->teacher('teacher-multi-class-create');
        [$firstClass, $secondClass] = $this->classes();
        $studentRole = StudentRole::create(['name' => 'Regular Student', 'is_active' => true]);

        $teacher->assignedClasses()->attach([$firstClass->id, $secondClass->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.students.store'), $this->studentPayload([
                'portal_id' => 'SELECTED-CLASS-001',
                'school_class_id' => $secondClass->id,
                'student_role_id' => $studentRole->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'portal_id' => 'SELECTED-CLASS-001',
            'school_class_id' => $secondClass->id,
        ]);
    }

    public function test_teacher_cannot_create_student_in_unassigned_class(): void
    {
        $teacher = $this->teacher('teacher-blocked-class-create');
        [$assignedClass, $outsideClass] = $this->classes();
        $studentRole = StudentRole::create(['name' => 'Regular Student', 'is_active' => true]);

        $teacher->assignedClasses()->attach($assignedClass->id);

        $this->actingAs($teacher)
            ->post(route('teacher.students.store'), $this->studentPayload([
                'portal_id' => 'BLOCKED-CLASS-001',
                'school_class_id' => $outsideClass->id,
                'student_role_id' => $studentRole->id,
            ]))
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['portal_id' => 'BLOCKED-CLASS-001']);
    }

    private function teacher(string $portalId): User
    {
        return User::create([
            'portal_id' => $portalId,
            'first_name' => 'Assigned',
            'last_name' => 'Teacher',
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<int, SchoolClass>
     */
    private function classes(): array
    {
        return [
            SchoolClass::create(['name' => 'JSS1A', 'level' => 'JSS1', 'stream' => 'A', 'is_active' => true]),
            SchoolClass::create(['name' => 'JSS1B', 'level' => 'JSS1', 'stream' => 'B', 'is_active' => true]),
            SchoolClass::create(['name' => 'JSS1C', 'level' => 'JSS1', 'stream' => 'C', 'is_active' => true]),
        ];
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'portal_id' => 'NEW-STUDENT-001',
            'name' => 'New Student',
            'account_type' => 'student',
            'student_role_id' => null,
            'age' => 12,
            'sex' => 'male',
            'complexion' => 'Fair',
            'password' => '12345',
        ], $overrides);
    }
}
