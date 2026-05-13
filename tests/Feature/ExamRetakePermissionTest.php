<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExamRetakePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_only_allow_retakes_for_exams_they_created(): void
    {
        [$exam, $student, $owner, $otherTeacher] = $this->examSetup();
        $attempt = $this->attemptFor($exam, $student);

        $this->actingAs($otherTeacher)
            ->delete(route('teacher.results.retakes.allow', [$exam, $attempt]))
            ->assertForbidden();

        $this->assertDatabaseHas('exam_attempts', ['id' => $attempt->id]);

        $this->actingAs($owner)
            ->delete(route('teacher.results.retakes.allow', [$exam, $attempt]))
            ->assertRedirect();

        $this->assertDatabaseMissing('exam_attempts', ['id' => $attempt->id]);
    }

    public function test_hod_and_cbt_can_allow_retakes_for_any_exam(): void
    {
        [$exam, $student] = $this->examSetup();
        $hod = $this->user('hod', 'hod-retake');
        $cbt = $this->user('cbt_personnel', 'cbt-retake');

        $hodAttempt = $this->attemptFor($exam, $student);
        $this->actingAs($hod)
            ->delete(route('hod.results.retakes.allow', [$exam, $hodAttempt]))
            ->assertRedirect();
        $this->assertDatabaseMissing('exam_attempts', ['id' => $hodAttempt->id]);

        $cbtAttempt = $this->attemptFor($exam, $student);
        $this->actingAs($cbt)
            ->delete(route('cbt.results.retakes.allow', [$exam, $cbtAttempt]))
            ->assertRedirect();
        $this->assertDatabaseMissing('exam_attempts', ['id' => $cbtAttempt->id]);
    }

    public function test_teacher_assigned_to_class_cannot_access_another_teachers_exam(): void
    {
        [$exam, , , $otherTeacher] = $this->examSetup();
        $exam->schoolClass()->update(['class_teacher_id' => $otherTeacher->id]);

        $this->actingAs($otherTeacher)
            ->get(route('teacher.exams'))
            ->assertOk()
            ->assertDontSee('Retake Test');

        $this->actingAs($otherTeacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->get(route('teacher.results.show', $exam))
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->get(route('teacher.ai-questions.exam-questions', $exam))
            ->assertForbidden();
    }

    private function examSetup(): array
    {
        $class = SchoolClass::create([
            'name' => 'JSS1 General',
            'level' => 'JSS1',
            'stream' => 'General',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH' . uniqid(),
            'school_class_id' => $class->id,
            'is_active' => true,
        ]);
        $owner = $this->user('teacher', 'teacher-owner');
        $otherTeacher = $this->user('teacher', 'teacher-other');
        $student = $this->user('student', 'student-retake', ['school_class_id' => $class->id]);

        $exam = Exam::create([
            'title' => 'Retake Test',
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'created_by' => $owner->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        return [$exam, $student, $owner, $otherTeacher];
    }

    private function attemptFor(Exam $exam, User $student): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(5),
            'score' => 5,
            'total_points' => 10,
            'percentage' => 50,
            'grade' => 'C-',
            'is_submitted' => true,
        ]);
    }

    private function user(string $role, string $portalId, array $overrides = []): User
    {
        return User::create(array_merge([
            'portal_id' => $portalId,
            'first_name' => ucfirst(str_replace('_', ' ', $role)),
            'last_name' => 'User',
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ], $overrides));
    }
}
