<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a test exam
$exam = App\Models\Exam::create([
    'title' => 'Test Mathematics Exam',
    'subject_id' => 1,
    'school_class_id' => 1,
    'created_by' => 1,
    'duration_minutes' => 60,
    'start_time' => now()->addDay(),
    'end_time' => now()->addDay()->addMinutes(60),
    'is_live' => false,
    'show_results' => true,
    'allow_review' => true
]);

echo "Test exam created with ID: " . $exam->id . "\n";

// Create a test question
$question = App\Models\Question::create([
    'exam_id' => $exam->id,
    'question_text' => 'What is 2 + 2?',
    'option_a' => '3',
    'option_b' => '4',
    'option_c' => '5',
    'option_d' => '6',
    'correct_answer' => 'b',
    'question_type' => 'multiple_choice',
    'points' => 1
]);

echo "Test question created with ID: " . $question->id . "\n";

echo "Test data created successfully!\n";
