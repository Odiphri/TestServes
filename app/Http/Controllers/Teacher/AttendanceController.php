<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Get teacher's assigned class
        $assignedClass = SchoolClass::where('class_teacher_id', $teacher->id)->first();
        
        if (!$assignedClass) {
            return view('teacher.attendance.no-class');
        }

        $students = User::where('role', 'student')
            ->where('school_class_id', $assignedClass->id)
            ->orderBy('last_name')
            ->get();

        $attendance = Attendance::with(['student', 'schoolClass'])
            ->where('school_class_id', $assignedClass->id)
            ->latest()
            ->paginate(20);

        return view('teacher.attendance.index', compact('assignedClass', 'students', 'attendance'));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();
        
        // Get teacher's assigned class
        $assignedClass = SchoolClass::where('class_teacher_id', $teacher->id)->first();
        
        if (!$assignedClass) {
            return back()->with('error', 'You are not assigned to any class.');
        }

        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'statuses' => 'required|array',
            'statuses.*' => 'required|in:present,absent',
        ]);

        foreach ($validated['statuses'] as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'school_class_id' => $assignedClass->id,
                    'marked_by' => Auth::id(),
                    'status' => $status,
                ]
            );
        }

        return back()->with('success', 'Attendance saved for ' . $assignedClass->full_name);
    }
}
