<?php

namespace App\Http\Controllers\CBT;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\JsonResponse;

class MonitorController extends Controller
{
    public function index()
    {
        $monitorData = $this->monitorData();
        $liveExams = $monitorData['live_exams'];
        $activeAttempts = $monitorData['active_attempts'];

        return view('cbt.monitor.index', compact('liveExams', 'activeAttempts'));
    }

    public function data(): JsonResponse
    {
        return response()->json($this->monitorData());
    }

    private function monitorData(): array
    {
        $liveExams = Exam::with(['subject', 'schoolClass'])
            ->where('is_live', true)
            ->withCount([
                'attempts',
                'attempts as active_attempts_count' => fn ($query) => $query->where('is_submitted', false),
                'attempts as submitted_attempts_count' => fn ($query) => $query->where('is_submitted', true),
            ])
            ->latest()
            ->get();

        $activeAttempts = ExamAttempt::with(['exam.subject', 'student.assignedClass'])
            ->where('is_submitted', false)
            ->whereHas('exam', fn ($query) => $query->where('is_live', true))
            ->latest()
            ->limit(50)
            ->get();

        return [
            'live_exams' => $liveExams->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject?->name ?? 'No subject',
                'class' => $exam->schoolClass?->full_name ?? 'No class',
                'active_attempts_count' => $exam->active_attempts_count,
                'submitted_attempts_count' => $exam->submitted_attempts_count,
            ])->values(),
            'active_attempts' => $activeAttempts->map(fn (ExamAttempt $attempt) => [
                'id' => $attempt->id,
                'student' => $attempt->student?->full_name ?? 'N/A',
                'class' => $attempt->student?->assignedClass?->full_name ?? 'N/A',
                'exam' => $attempt->exam?->title ?? 'N/A',
                'started_at' => $attempt->started_at?->format('M d, H:i') ?? 'N/A',
                'last_seen' => $attempt->updated_at?->format('M d, H:i') ?? 'N/A',
                'expires_at' => $attempt->time_expired_at?->format('M d, H:i') ?? 'N/A',
            ])->values(),
            'refreshed_at' => now()->format('M d, H:i:s'),
        ];
    }
}
