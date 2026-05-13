<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\FeeItem;
use App\Models\Question;
use App\Models\Payment;
use App\Models\Override;
use App\Models\StudentFeeExemption;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExamController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $studentClass = $student->assignedClass;
        
        if (!$studentClass) {
            return view('student.exams.index', [
                'exams' => collect(),
                'noClass' => true,
            ]);
        }
        
        $exams = Exam::where('school_class_id', $studentClass->id)
            ->where('is_live', true)
            ->where(function ($query) {
                $query->whereNull('start_time')
                    ->orWhere('start_time', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>=', now());
            })
            ->with(['subject', 'attempts' => function($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->get();
            
        // Check if student can access each exam
        $isOwing = $this->studentIsOwing($student);

        $exams->each(function($exam) use ($student, $isOwing) {
            $exam->active_override = $this->activeOverrideForExam($exam, $student);
            $exam->is_owing = $isOwing;
            $exam->can_access = $this->canAccessExam($exam, $student);
            $exam->attempted = $exam->attempts->isNotEmpty();
            $exam->attempt = $exam->attempts->first();
        });
        
        return view('student.exams.index', [
            'exams' => $exams,
            'noClass' => false,
            'isOwing' => $isOwing,
            'hasActiveOverride' => $exams->contains(fn ($exam) => (bool) $exam->active_override),
        ]);
    }
    
    public function show(Exam $exam)
    {
        $student = Auth::user();
        
        if (!$this->canAccessExam($exam, $student)) {
            return redirect()->route('student.exams')
                ->with('error', 'You cannot access this exam.');
        }
        
        // Check if student has already attempted this exam
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();
            
        if ($existingAttempt && $existingAttempt->is_submitted) {
            return redirect()->route('student.exams.results', $exam->id)
                ->with('info', 'You have already submitted this exam.');
        }
        
        $questions = Question::where('exam_id', $exam->id)
            ->inRandomOrder()
            ->get();
            
        return view('student.exams.take', compact('exam', 'questions'));
    }
    
    public function store(Request $request, Exam $exam)
    {
        $student = Auth::user();
        
        if (!$this->canAccessExam($exam, $student)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Validate request
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string'
        ]);
        
        // Create or update exam attempt
        $attempt = ExamAttempt::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'started_at' => now(),
                'time_expired_at' => now()->addMinutes($exam->duration_minutes),
                'total_points' => $exam->questions()->sum('points'),
                'answers' => $request->answers,
                'is_submitted' => false,
            ]
        );
        
        return response()->json([
            'success' => true,
            'attempt_id' => $attempt->id,
            'expire_time' => $attempt->time_expired_at
        ]);
    }
    
    public function submit(Request $request, Exam $exam)
    {
        $student = Auth::user();

        if (!$this->canAccessExam($exam, $student)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $answers = $request->input('answers', []);
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?: [];
        }

        // Get or create the attempt
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();
            
        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => now(),
                'time_expired_at' => now()->addMinutes($exam->duration_minutes),
                'total_points' => $exam->questions()->sum('points'),
                'answers' => $answers,
                'is_submitted' => false,
            ]);
        }
        
        if ($attempt->is_submitted) {
            return response()->json(['error' => 'Exam already submitted'], 400);
        }
        
        // Calculate score
        $questions = Question::where('exam_id', $exam->id)->get();
        
        $totalPoints = 0;
        $scoredPoints = 0;
        
        foreach ($questions as $question) {
            $totalPoints += $question->points;
            
            if (isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer) {
                $scoredPoints += $question->points;
            }
        }
        
        $percentage = $totalPoints > 0 ? ($scoredPoints / $totalPoints) * 100 : 0;
        $grade = $this->calculateGrade($percentage);
        
        // Update attempt
        $attempt->update([
            'submitted_at' => now(),
            'score' => $scoredPoints,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'grade' => $grade,
            'answers' => $answers,
            'is_submitted' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'score' => $scoredPoints,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'grade' => $grade
        ]);
    }
    
    public function results(Exam $exam)
    {
        $student = Auth::user();

        if (!$this->studentBelongsToExamClass($exam, $student)) {
            return redirect()->route('student.exams')
                ->with('error', 'You cannot view this exam result.');
        }
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('is_submitted', true)
            ->first();
            
        if (!$attempt) {
            return redirect()->route('student.exams')
                ->with('error', 'No submitted attempt found for this exam.');
        }
        
        // Check if results are visible
        if (!$exam->show_results) {
            return view('student.exams.results-hidden', compact('exam'));
        }
        
        $questions = Question::where('exam_id', $exam->id)->get();
        $answers = is_array($attempt->answers)
            ? $attempt->answers
            : (json_decode($attempt->answers, true) ?: []);
        
        return view('student.exams.results', compact('exam', 'attempt', 'questions', 'answers'));
    }
    
    private function canAccessExam(Exam $exam, $student)
    {
        if (!$this->studentBelongsToExamClass($exam, $student)) {
            return false;
        }

        if (!$exam->is_live) {
            return false;
        }

        $now = now();

        if ($exam->start_time && $exam->start_time->gt($now)) {
            return false;
        }

        if ($exam->end_time && $exam->end_time->lt($now)) {
            return false;
        }

        return ! $this->studentIsOwing($student) || (bool) $this->activeOverrideForExam($exam, $student);
    }

    private function studentIsOwing($student): bool
    {
        if (! $student->school_class_id) {
            return false;
        }

        $payment = Payment::where('student_id', $student->id)
            ->where('school_class_id', $student->school_class_id)
            ->latest('updated_at')
            ->first();

        if ($payment && ($payment->status !== 'paid' || (float) $payment->balance > 0)) {
            return true;
        }

        $activeFees = FeeItem::active()->with('classes')->get();
        $removedFeeIds = StudentFeeExemption::where('student_id', $student->id)->pluck('fee_item_id')->all();
        $totalDue = (float) $activeFees
            ->filter(fn (FeeItem $fee) => $fee->appliesToStudent($student))
            ->reject(fn (FeeItem $fee) => $fee->isOptional() && in_array($fee->id, $removedFeeIds, true))
            ->sum('amount');

        if ($totalDue <= 0) {
            return false;
        }

        return ! $payment || (float) $payment->amount_paid < $totalDue;
    }

    private function activeOverrideForExam(Exam $exam, $student): ?Override
    {
        return Override::active()
            ->where('student_id', $student->id)
            ->where(function ($query) use ($exam) {
                $query->whereNull('exam_id')
                    ->orWhere('exam_id', $exam->id);
            })
            ->latest('updated_at')
            ->first();
    }

    private function studentBelongsToExamClass(Exam $exam, $student): bool
    {
        return $student->school_class_id
            && (int) $student->school_class_id === (int) $exam->school_class_id;
    }
    
    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        return 'F';
    }
}
