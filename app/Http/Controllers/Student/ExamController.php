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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

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

        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt && $existingAttempt->is_submitted) {
            return redirect()->route($this->routePrefix() . '.exams.results', $exam->id)
                ->with('info', 'You have already submitted this exam.');
        }

        if (!$this->canAccessExam($exam, $student)) {
            return redirect()->route($this->routePrefix() . '.exams')
                ->with('error', 'You cannot access this exam.');
        }

        if ($existingAttempt && $this->attemptHasExpired($existingAttempt)) {
            $this->finalizeAttempt($existingAttempt, $exam, $existingAttempt->answers ?? []);

            return redirect()->route($this->routePrefix() . '.exams.results', $exam->id)
                ->with('info', 'Time has expired for this exam. Your saved answers were submitted.');
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
            
        $answers = is_array($attempt->answers)
            ? $attempt->answers
            : (json_decode($attempt->answers, true) ?: []);

        return $this->withoutBrowserCache(view('student.exams.take', [
            'exam' => $exam,
            'questions' => $questions,
            'attempt' => $attempt,
            'answers' => $answers,
            'remainingSeconds' => $this->remainingSeconds($attempt),
            'examRoutePrefix' => $this->routePrefix(),
        ]));
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
            'answers.*' => 'required|string|in:A,B,C,D'
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

        if ($attempt->is_submitted) {
            return response()->json(['error' => 'Exam already submitted'], 409);
        }

        if ($this->attemptHasExpired($attempt)) {
            $this->finalizeAttempt($attempt, $exam, $attempt->answers ?? []);

            return response()->json([
                'success' => false,
                'expired' => true,
                'redirect' => route($this->routePrefix() . '.exams.results', $exam),
                'error' => 'Time has expired. Your saved answers were submitted.',
            ], 409);
        }

        $attempt->update([
            'total_points' => $exam->questions()->sum('points'),
            'answers' => $request->answers,
        ]);
        
        return response()->json([
            'success' => true,
            'attempt_id' => $attempt->id,
            'expire_time' => $attempt->time_expired_at,
            'remaining_seconds' => $this->remainingSeconds($attempt),
        ]);
    }
    
    public function submit(Request $request, Exam $exam)
    {
        $student = Auth::user();

        if (!$this->studentBelongsToExamClass($exam, $student)) {
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
            if (!$this->canAccessExam($exam, $student)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

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
            return response()->json([
                'success' => true,
                'already_submitted' => true,
                'redirect' => route($this->routePrefix() . '.exams.results', $exam),
            ]);
        }

        if ($this->attemptHasExpired($attempt)) {
            $answers = $attempt->answers ?? [];
        }

        $attempt = $this->finalizeAttempt($attempt, $exam, $answers);
        
        return response()->json([
            'success' => true,
            'score' => $attempt->score,
            'total_points' => $attempt->total_points,
            'percentage' => $attempt->percentage,
            'grade' => $attempt->grade,
            'redirect' => route($this->routePrefix() . '.exams.results', $exam),
        ]);
    }
    
    public function results(Exam $exam)
    {
        $student = Auth::user();

        if (!$this->studentBelongsToExamClass($exam, $student)) {
            return redirect()->route($this->routePrefix() . '.exams')
                ->with('error', 'You cannot view this exam result.');
        }
        
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('is_submitted', true)
            ->first();
            
        if (!$attempt) {
            return redirect()->route($this->routePrefix() . '.exams')
                ->with('error', 'No submitted attempt found for this exam.');
        }
        
        // Check if results are visible
        if (!$exam->show_results) {
            return $this->withoutBrowserCache(view('student.exams.results-hidden', [
                'exam' => $exam,
                'examRoutePrefix' => $this->routePrefix(),
            ]));
        }
        
        $questions = Question::where('exam_id', $exam->id)->get();
        $answers = is_array($attempt->answers)
            ? $attempt->answers
            : (json_decode($attempt->answers, true) ?: []);
        
        return $this->withoutBrowserCache(view('student.exams.results', compact('exam', 'attempt', 'questions', 'answers') + [
            'examRoutePrefix' => $this->routePrefix(),
        ]));
    }

    private function remainingSeconds(ExamAttempt $attempt): int
    {
        if ($attempt->is_submitted) {
            return 0;
        }

        $expiresAt = $attempt->time_expired_at
            ?: $attempt->started_at->copy()->addMinutes($attempt->exam->duration_minutes);

        return max(0, now()->diffInSeconds($expiresAt, false));
    }

    private function attemptHasExpired(ExamAttempt $attempt): bool
    {
        return $this->remainingSeconds($attempt) <= 0;
    }

    private function finalizeAttempt(ExamAttempt $attempt, Exam $exam, array $answers): ExamAttempt
    {
        return DB::transaction(function () use ($attempt, $exam, $answers) {
            $attempt = ExamAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($attempt->is_submitted) {
                return $attempt;
            }

            $questions = Question::where('exam_id', $exam->id)->get();

            $totalPoints = 0;
            $scoredPoints = 0;

            foreach ($questions as $question) {
                $totalPoints += $question->points;

                $given = $answers[$question->id] ?? null;

                if ($given !== null && $question->isCorrectAnswer((string) $given)) {
                    $scoredPoints += $question->points;
                }
            }

            $percentage = $totalPoints > 0 ? ($scoredPoints / $totalPoints) * 100 : 0;

            $attempt->update([
                'submitted_at' => now(),
                'score' => $scoredPoints,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'answers' => $answers,
                'is_submitted' => true,
            ]);

            return $attempt->fresh();
        });
    }

    private function withoutBrowserCache($response)
    {
        return response($response->render())->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
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
