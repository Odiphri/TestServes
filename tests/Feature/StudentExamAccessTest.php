<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\FeeItem;
use App\Models\Override;
use App\Models\Payment;
use App\Models\Question;
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

    public function test_student_sees_exam_targeted_to_their_arm_or_stream(): void
    {
        $jss1A = SchoolClass::create(['name' => 'JSS1A', 'level' => 'JSS1', 'stream' => 'A', 'is_active' => true]);
        $jss1B = SchoolClass::create(['name' => 'JSS1B', 'level' => 'JSS1', 'stream' => 'B', 'is_active' => true]);
        $jss1C = SchoolClass::create(['name' => 'JSS1C', 'level' => 'JSS1', 'stream' => 'C', 'is_active' => true]);
        $subject = Subject::create(['name' => 'Math', 'code' => 'MTH-AB', 'school_class_id' => $jss1A->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-target',
            'first_name' => 'Teacher',
            'last_name' => 'Target',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $exam = Exam::create([
            'title' => 'JSS1 A and B Math',
            'subject_id' => $subject->id,
            'school_class_id' => $jss1A->id,
            'target_class_ids' => [$jss1A->id, $jss1B->id],
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        $studentB = User::create([
            'portal_id' => 'student-target-b',
            'first_name' => 'Student',
            'last_name' => 'Bee',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $jss1B->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $studentC = User::create([
            'portal_id' => 'student-target-c',
            'first_name' => 'Student',
            'last_name' => 'Cee',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $jss1C->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($studentB)
            ->get('/student/exams')
            ->assertOk()
            ->assertSee('JSS1 A and B Math');

        $this->actingAs($studentB)->get("/student/exams/{$exam->id}")->assertOk();

        $this->actingAs($studentC)
            ->get('/student/exams')
            ->assertOk()
            ->assertDontSee('JSS1 A and B Math');

        $this->actingAs($studentC)->get("/student/exams/{$exam->id}")->assertRedirect('/student/exams');
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

    public function test_exam_submission_scores_correct_answers(): void
    {
        [$student, $exam, $questions] = $this->createScoringExam();

        $this->actingAs($student)
            ->postJson("/student/exams/{$exam->id}/submit", [
                'answers' => [
                    (string) $questions[0]->id => 'b',
                    (string) $questions[1]->id => 'D',
                ],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'score' => 5,
                'total_points' => 5,
            ]);

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'is_submitted' => true,
            'score' => 5,
        ]);
    }

    public function test_empty_final_submit_uses_saved_answers_instead_of_grading_zero(): void
    {
        [$student, $exam, $questions] = $this->createScoringExam();

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'time_expired_at' => now()->addMinutes($exam->duration_minutes),
            'total_points' => 5,
            'answers' => [
                (string) $questions[0]->id => 'B',
                (string) $questions[1]->id => 'D',
            ],
            'is_submitted' => false,
        ]);

        $this->actingAs($student)
            ->postJson("/student/exams/{$exam->id}/submit", [
                'answers' => [],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'score' => 5,
                'total_points' => 5,
            ]);

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'is_submitted' => true,
            'score' => 5,
        ]);
    }

    private function createScoringExam(): array
    {
        $class = SchoolClass::create(['name' => 'JSS1 General', 'level' => 'JSS1', 'stream' => 'General', 'is_active' => true]);
        $subject = Subject::create(['name' => 'Mathematics', 'code' => 'MTH-SCORE-' . uniqid(), 'school_class_id' => $class->id, 'is_active' => true]);
        $teacher = User::create([
            'portal_id' => 'teacher-score-' . uniqid(),
            'first_name' => 'Teacher',
            'last_name' => 'Score',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student = User::create([
            'portal_id' => 'student-score-' . uniqid(),
            'first_name' => 'Student',
            'last_name' => 'Score',
            'password' => Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $class->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $exam = Exam::create([
            'title' => 'Scoring Test',
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 30,
            'is_live' => true,
            'show_results' => true,
            'shuffle_questions' => false,
            'pass_mark' => 50,
        ]);

        $questions = [
            Question::create([
                'exam_id' => $exam->id,
                'question_text' => 'One plus one?',
                'option_a' => '1',
                'option_b' => '2',
                'option_c' => '3',
                'option_d' => '4',
                'correct_answer' => 'B',
                'points' => 2,
            ]),
            Question::create([
                'exam_id' => $exam->id,
                'question_text' => 'Last option is correct?',
                'option_a' => 'A',
                'option_b' => 'B',
                'option_c' => 'C',
                'option_d' => 'D',
                'correct_answer' => 'D',
                'points' => 3,
            ]),
        ];

        return [$student, $exam, $questions];
    }
}
