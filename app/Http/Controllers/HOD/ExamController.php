<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Exam;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['subject', 'schoolClass', 'creator'])
            ->withCount(['questions', 'attempts'])
            ->withAvg('attempts', 'percentage')
            ->latest()
            ->paginate(20);
        $routePrefix = 'hod';

        return view('teacher.exams.index', compact('exams', 'routePrefix'));
    }
}
