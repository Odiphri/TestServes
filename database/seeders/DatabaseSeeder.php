<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Payment;
use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OnboardAdminSeeder::class,
        ]);

        // Create Admin User
        $admin = User::create([
            'portal_id' => 'ADMIN001',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');
        
        Profile::create(['user_id' => $admin->id]);

        // Create HOD
        $hod = User::create([
            'portal_id' => 'HOD001',
            'first_name' => 'Academic',
            'last_name' => 'Director',
            'email' => 'hod@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'hod',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $hod->assignRole('hod');
        Profile::create(['user_id' => $hod->id]);

        // Create CBT Personnel
        $cbt = User::create([
            'portal_id' => 'CBT001',
            'first_name' => 'Exam',
            'last_name' => 'Officer',
            'email' => 'cbt@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'cbt_personnel',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $cbt->assignRole('cbt_personnel');
        Profile::create(['user_id' => $cbt->id]);

        // Create Teacher
        $teacher = User::create([
            'portal_id' => 'TCH001',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'teacher@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $teacher->assignRole('teacher');
        Profile::create(['user_id' => $teacher->id]);

        // Create Prefect
        $prefect = User::create([
            'portal_id' => 'PRF001',
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'prefect@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'prefect',
            'prefect_title' => 'Head Girl',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $prefect->assignRole('prefect');
        Profile::create(['user_id' => $prefect->id]);

        // Create Student
        $student = User::create([
            'portal_id' => 'STU001',
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'student@testserves.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $student->assignRole('student');
        Profile::create(['user_id' => $student->id]);

        // Create Classes
        $classes = [
            ['name' => 'JSS1A', 'level' => 'JSS1', 'stream' => 'A'],
            ['name' => 'JSS2A', 'level' => 'JSS2', 'stream' => 'A'],
            ['name' => 'JSS3A', 'level' => 'JSS3', 'stream' => 'A'],
            ['name' => 'SS1 Science', 'level' => 'SS1', 'stream' => 'Science'],
            ['name' => 'SS1 Art', 'level' => 'SS1', 'stream' => 'Art'],
            ['name' => 'SS1 Commercial', 'level' => 'SS1', 'stream' => 'Commercial'],
            ['name' => 'SS2 Science', 'level' => 'SS2', 'stream' => 'Science'],
            ['name' => 'SS2 Art', 'level' => 'SS2', 'stream' => 'Art'],
            ['name' => 'SS2 Commercial', 'level' => 'SS2', 'stream' => 'Commercial'],
            ['name' => 'SS3 Science', 'level' => 'SS3', 'stream' => 'Science'],
            ['name' => 'SS3 Art', 'level' => 'SS3', 'stream' => 'Art'],
            ['name' => 'SS3 Commercial', 'level' => 'SS3', 'stream' => 'Commercial'],
        ];

        foreach ($classes as $classData) {
            SchoolClass::create($classData);
        }

        $ss1Science = SchoolClass::where('name', 'SS1 Science')->first();
        $student->update(['school_class_id' => $ss1Science->id]);

        // Create Subjects for SS1 Science
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH401', 'school_class_id' => $ss1Science->id],
            ['name' => 'English Language', 'code' => 'ENG401', 'school_class_id' => $ss1Science->id],
            ['name' => 'Physics', 'code' => 'PHY401', 'school_class_id' => $ss1Science->id],
            ['name' => 'Chemistry', 'code' => 'CHEM401', 'school_class_id' => $ss1Science->id],
            ['name' => 'Biology', 'code' => 'BIO401', 'school_class_id' => $ss1Science->id],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create($subjectData);
        }

        // Create a sample exam
        $mathSubject = Subject::where('code', 'MATH401')->first();
        $exam = Exam::create([
            'title' => 'Mathematics Mid-Term Exam',
            'description' => 'SS1 Science Mathematics Mid-Term Examination',
            'subject_id' => $mathSubject->id,
            'school_class_id' => $ss1Science->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 120,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'shuffle_questions' => true,
            'show_results' => true,
            'is_live' => false,
            'pass_mark' => 50,
        ]);

        // Create sample questions
        $questions = [
            [
                'exam_id' => $exam->id,
                'question_text' => 'What is the value of 2 + 2?',
                'option_a' => '3',
                'option_b' => '4',
                'option_c' => '5',
                'option_d' => '6',
                'correct_answer' => 'B',
                'points' => 1,
                'order' => 1,
            ],
            [
                'exam_id' => $exam->id,
                'question_text' => 'What is the square root of 16?',
                'option_a' => '2',
                'option_b' => '3',
                'option_c' => '4',
                'option_d' => '5',
                'correct_answer' => 'C',
                'points' => 1,
                'order' => 2,
            ],
            [
                'exam_id' => $exam->id,
                'question_text' => 'What is 5 × 6?',
                'option_a' => '25',
                'option_b' => '30',
                'option_c' => '35',
                'option_d' => '40',
                'correct_answer' => 'B',
                'points' => 1,
                'order' => 3,
            ],
        ];

        foreach ($questions as $questionData) {
            Question::create($questionData);
        }

        // Create payment record for student
        Payment::create([
            'student_id' => $student->id,
            'school_class_id' => $ss1Science->id,
            'total_fees' => 50000.00,
            'amount_paid' => 25000.00,
            'status' => 'partial',
            'payment_details' => 'First term payment',
            'last_payment_date' => now(),
        ]);

        echo "Database seeded successfully!\n";
        echo "Login credentials:\n";
        echo "Admin: portal_id=ADMIN001, password=password\n";
        echo "HOD: portal_id=HOD001, password=password\n";
        echo "CBT: portal_id=CBT001, password=password\n";
        echo "Teacher: portal_id=TCH001, password=password\n";
        echo "Prefect: portal_id=PRF001, password=password\n";
        echo "Student: portal_id=STU001, password=password\n";
    }
}
