<?php

namespace App\Http\Controllers\CBT;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;

class MonitorController extends Controller
{
    public function index()
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
            ->paginate(20);

        return view('cbt.monitor.index', compact('liveExams', 'activeAttempts'));
    }
}
