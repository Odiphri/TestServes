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
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ExamController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $eligibleClassIds = $this->eligibleClassIds($student);
        
        if ($eligibleClassIds->isEmpty()) {
            return view('student.exams.index', [
                'exams' => collect(),
                'noClass' => true,
                'examRoutePrefix' => $this->routePrefix(),
            ]);
        }
        
        $exams = Exam::query()
            ->where(fn ($query) => $this->whereExamTargetsAnyClass($query, $eligibleClassIds))
            ->where('is_live', true)
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
            'noClass' => $eligibleClassIds->isEmpty(),
            'isOwing' => $isOwing,
            'hasActiveOverride' => $exams->contains(fn ($exam) => (bool) $exam->active_override),
            'examRoutePrefix' => $this->routePrefix(),
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

        $attempt = $existingAttempt ?: ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'time_expired_at' => now()->addMinutes($exam->duration_minutes),
            'total_points' => $exam->questions()->sum('points'),
            'answers' => [],
            'is_submitted' => false,
        ]);
        
        $questions = Question::where('exam_id', $exam->id)
            ->inRandomOrder()
            ->get();
            
        return view('student.exams.take', [
            'exam' => $exam,
            'questions' => $questions,
            'attempt' => $attempt,
            'examRoutePrefix' => $this->routePrefix(),
        ]);
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
        
        $attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'started_at' => now(),
                'time_expired_at' => now()->addMinutes($exam->duration_minutes),
                'total_points' => $exam->questions()->sum('points'),
                'answers' => [],
                'is_submitted' => false,
            ]
        );

        if (! $attempt->is_submitted) {
            $attempt->update([
                'total_points' => $exam->questions()->sum('points'),
                'answers' => $request->answers,
            ]);
        }
        
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
            return view('student.exams.results-hidden', [
                'exam' => $exam,
                'examRoutePrefix' => $this->routePrefix(),
            ]);
        }
        
        $questions = Question::where('exam_id', $exam->id)->get();
        $answers = is_array($attempt->answers)
            ? $attempt->answers
            : (json_decode($attempt->answers, true) ?: []);
        
        return view('student.exams.results', compact('exam', 'attempt', 'questions', 'answers') + [
            'examRoutePrefix' => $this->routePrefix(),
        ]);
    }
    
    private function canAccessExam(Exam $exam, $student)
    {
        if (!$this->studentBelongsToExamClass($exam, $student)) {
            return false;
        }

        if (!$exam->is_live) {
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
        $eligibleClassIds = $this->eligibleClassIds($student);

        if ($eligibleClassIds->isEmpty()) {
            return false;
        }

        return $eligibleClassIds->contains(fn ($classId) => $exam->targetsClass((int) $classId));
    }

    private function whereExamTargetsAnyClass($query, Collection $classIds)
    {
        return $query->where(function ($query) use ($classIds) {
            $query->whereIn('school_class_id', $classIds);

            foreach ($classIds as $classId) {
                $query->orWhereJsonContains('target_class_ids', (int) $classId);
            }
        });
    }

    private function eligibleClassIds($student): Collection
    {
        return collect([$student->school_class_id])
            ->merge($student->subjects()->pluck('subjects.school_class_id'))
            ->filter()
            ->map(fn ($classId) => (int) $classId)
            ->unique()
            ->values();
    }

    private function routePrefix(): string
    {
        return str_starts_with((string) request()->route()->getName(), 'prefect.') ? 'prefect' : 'student';
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
