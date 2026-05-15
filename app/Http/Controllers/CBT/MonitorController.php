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
            ->where(function ($query) {
                $query->whereNull('start_time')
                    ->orWhere('start_time', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>=', now());
            })
            ->latest()
            ->get();

        $activeAttempts = ExamAttempt::with(['exam.subject', 'student.assignedClass'])
            ->where('is_submitted', false)
            ->where(function ($query) {
                $query->whereNull('time_expired_at')
                    ->orWhere('time_expired_at', '>=', now());
            })
            ->latest()
            ->paginate(20);

        return view('cbt.monitor.index', compact('liveExams', 'activeAttempts'));
    }
}
