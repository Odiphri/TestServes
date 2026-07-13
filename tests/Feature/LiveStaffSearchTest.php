<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LiveStaffSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_links_to_contact_page_without_plain_email(): void
    {
        $this->get(route('privacy.policy'))
            ->assertOk()
            ->assertSee('Contact Us')
            ->assertDontSee('testserves.ng@gmail.com');
    }

    public function test_admin_user_search_filters_without_losing_live_search_target(): void
    {
        $admin = $this->user('admin', 'admin-search', 'Admin Search');
        $visibleTeacher = $this->user('teacher', 'visible-teacher', 'Visible Teacher');
        $hiddenStudent = $this->user('student', 'hidden-student', 'Hidden Student');

        $this->actingAs($admin)
            ->get(route('admin.users', ['search' => 'VISIBLE']), [
                'X-Requested-With' => 'XMLHttpRequest',
                'X-Live-Search' => '1',
            ])
            ->assertOk()
            ->assertSee('id="users-results"', false)
            ->assertSee($visibleTeacher->full_name)
            ->assertDontSee($hiddenStudent->full_name);
    }

    public function test_teacher_exam_search_filters_by_exam_title_and_subject(): void
    {
        $teacher = $this->user('teacher', 'teacher-exam-search', 'Exam Teacher');
        $class = SchoolClass::create(['name' => 'JSS1A', 'level' => 'JSS1', 'stream' => 'A', 'is_active' => true]);
        $math = Subject::create(['name' => 'Mathematics', 'code' => 'MATH-LIVE', 'school_class_id' => $class->id, 'is_active' => true]);
        $english = Subject::create(['name' => 'English', 'code' => 'ENG-LIVE', 'school_class_id' => $class->id, 'is_active' => true]);

        Exam::create([
            'title' => 'Algebra Assessment',
            'subject_id' => $math->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        Exam::create([
            'title' => 'Comprehension Assessment',
            'subject_id' => $english->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams', ['search' => 'algebra']), [
                'X-Requested-With' => 'XMLHttpRequest',
                'X-Live-Search' => '1',
            ])
            ->assertOk()
            ->assertSee('id="exams-results"', false)
            ->assertSee('Algebra Assessment')
            ->assertDontSee('Comprehension Assessment');
    }

    private function user(string $role, string $portalId, string $name): User
    {
        [$firstName, $lastName] = explode(' ', $name, 2);

        return User::create([
            'portal_id' => $portalId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }
}
