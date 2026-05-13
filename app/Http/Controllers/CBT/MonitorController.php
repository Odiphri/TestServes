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
            ->latest()
            ->get();

        $activeAttempts = ExamAttempt::with(['exam', 'student'])
            ->where('is_submitted', false)
            ->latest()
            ->paginate(20);

        return view('cbt.monitor.index', compact('liveExams', 'activeAttempts'));
    }
}
