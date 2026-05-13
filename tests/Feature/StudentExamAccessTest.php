<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\FeeItem;
use App\Models\Override;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentExamAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_sees_and_takes_live_exams_for_their_class(): void
    {
        $jss1 = SchoolClass::create(['name' => 'JSS1 General', 'level' => 'JSS1', 'stream' => 'General', 'is_active' => true]);
        $jss2 = SchoolClass::create(['name' => 'JSS2 General', 'level' => 'JSS2', 'stream' => 'General', 'is_active' => true]);
        $jss1Subject = Subject::create(['name' => 'Math', 'code' => 'MTH1', 'school_class_id' => $jss1->id, 'is_active' => true]);
        $jss2Subject = Subject::create(['name' => 'English', 'code' => 'ENG2', 'school_class_id' => $jss2->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-smoke',
            'first_name' => 'Teacher',
            'last_name' => 'Smoke',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student = User::create([
            'portal_id' => 'student-smoke',
            'first_name' => 'Student',
            'last_name' => 'Smoke',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $jss1->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $ownExam = Exam::create([
            'title' => 'JSS1 Math',
            'subject_id' => $jss1Subject->id,
            'school_class_id' => $jss1->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);
        $otherExam = Exam::create([
            'title' => 'JSS2 English',
            'subject_id' => $jss2Subject->id,
            'school_class_id' => $jss2->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        $this->actingAs($student)
            ->get('/student/exams')
            ->assertOk()
            ->assertSee('JSS1 Math')
            ->assertDontSee('JSS2 English');

        $this->actingAs($student)->get("/student/exams/{$ownExam->id}")->assertOk();
        $this->actingAs($student)->get("/student/exams/{$otherExam->id}")->assertRedirect('/student/exams');
    }

    public function test_student_never_sees_or_takes_draft_exams(): void
    {
        $class = SchoolClass::create(['name' => 'JSS1 General', 'level' => 'JSS1', 'stream' => 'General', 'is_active' => true]);
        $subject = Subject::create(['name' => 'Math', 'code' => 'MTH-DRAFT', 'school_class_id' => $class->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-draft',
            'first_name' => 'Teacher',
            'last_name' => 'Draft',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student = User::create([
            'portal_id' => 'student-draft',
            'first_name' => 'Student',
            'last_name' => 'Draft',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $class->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $draftExam = Exam::create([
            'title' => 'Draft Teacher Exam',
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => false,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk()
            ->assertDontSee('Draft Teacher Exam')
            ->assertDontSee('badge bg-secondary');

        $this->actingAs($student)
            ->get('/student/exams')
            ->assertOk()
            ->assertDontSee('Draft Teacher Exam');

        $this->actingAs($student)
            ->get("/student/exams/{$draftExam->id}")
            ->assertRedirect('/student/exams');
    }

    public function test_owing_student_cannot_take_exam_without_active_override(): void
    {
        $class = SchoolClass::create(['name' => 'JSS1 General', 'level' => 'JSS1', 'stream' => 'General', 'is_active' => true]);
        $subject = Subject::create(['name' => 'Math', 'code' => 'MTH-OWE', 'school_class_id' => $class->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-owe',
            'first_name' => 'Teacher',
            'last_name' => 'Owe',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student = User::create([
            'portal_id' => 'student-owe',
            'first_name' => 'Student',
            'last_name' => 'Owe',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $class->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $exam = Exam::create([
            'title' => 'Owing Test',
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        FeeItem::create([
            'name' => 'School Fees',
            'amount' => 1000,
            'fee_type' => 'compulsory',
            'applies_to_all_classes' => true,
            'created_by' => $teacher->id,
            'is_active' => true,
        ]);
        Payment::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'total_fees' => 1000,
            'amount_paid' => 300,
            'status' => 'partial',
        ]);

        $this->actingAs($student)
            ->get("/student/exams/{$exam->id}")
            ->assertRedirect('/student/exams');

        Override::create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'approved_by' => $teacher->id,
            'reason' => 'Approved for exam',
            'expiry_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->get("/student/exams/{$exam->id}")
            ->assertOk();
    }

    public function test_student_with_partial_payment_record_cannot_take_exam_until_paid(): void
    {
        $class = SchoolClass::create(['name' => 'JSS2 General', 'level' => 'JSS2', 'stream' => 'General', 'is_active' => true]);
        $subject = Subject::create(['name' => 'Basic Science', 'code' => 'BSC-PART', 'school_class_id' => $class->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-partial',
            'first_name' => 'Teacher',
            'last_name' => 'Partial',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student = User::create([
            'portal_id' => 'student-partial',
            'first_name' => 'Student',
            'last_name' => 'Partial',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $class->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $exam = Exam::create([
            'title' => 'Partial Payment Test',
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        Payment::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'total_fees' => 1000,
            'amount_paid' => 999,
            'status' => 'partial',
        ]);

        $this->actingAs($student)
            ->get("/student/exams/{$exam->id}")
            ->assertRedirect('/student/exams');

        Payment::where('student_id', $student->id)->update([
            'amount_paid' => 1000,
            'status' => 'paid',
        ]);

        $this->actingAs($student)
            ->get("/student/exams/{$exam->id}")
            ->assertOk();
    }
}
