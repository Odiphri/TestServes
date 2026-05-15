<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();

        return view('teacher.dashboard', [
            'myExamsCount' => Exam::where('created_by', $teacher->id)->count(),
            'liveExamsCount' => Exam::where('created_by', $teacher->id)->where('is_live', true)->count(),
            'attendanceMarkedCount' => Attendance::where('marked_by', $teacher->id)->count(),
        ]);
    }
}
