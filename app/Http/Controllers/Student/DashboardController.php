<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $studentClass = $student->assignedClass;
        
        if (!$studentClass) {
            return view('student.dashboard', [
                'student' => $student,
                'recentExams' => collect(),
                'examStats' => [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0
                ],
                'paymentStatus' => null,
                'attendanceStats' => [
                    'present' => 0,
                    'absent' => 0,
                    'total' => 0
                ]
            ]);
        }
        
        // Get recent exams
        $recentExams = Exam::where('school_class_id', $studentClass->id)
            ->with(['subject', 'attempts' => function($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Calculate exam statistics
        $totalExams = Exam::where('school_class_id', $studentClass->id)->count();
        $completedExams = ExamAttempt::where('student_id', $student->id)
            ->where('is_submitted', true)
            ->count();
        $pendingExams = $totalExams - $completedExams;
        
        $examStats = [
            'total' => $totalExams,
            'completed' => $completedExams,
            'pending' => $pendingExams
        ];
        
        // Get payment status
        $paymentStatus = Payment::where('student_id', $student->id)
            ->where('school_class_id', $studentClass->id)
            ->first();
            
        // Get attendance statistics
        $attendanceStats = [
            'present' => Attendance::where('student_id', $student->id)
                ->where('status', 'present')
                ->count(),
            'absent' => Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->count(),
            'total' => Attendance::where('student_id', $student->id)->count()
        ];
        
        return view('student.dashboard', compact(
            'student',
            'recentExams',
            'examStats',
            'paymentStatus',
            'attendanceStats'
        ));
    }

    public function payments()
    {
        $student = Auth::user();
        
        $payments = Payment::where('student_id', $student->id)
            ->with('schoolClass')
            ->latest()
            ->get();
            
        $totalFees = $payments->sum('total_fees');
        $paidAmount = $payments->sum('amount_paid');
        $balance = $totalFees - $paidAmount;
        
        return view('student.payments.index', compact('payments', 'totalFees', 'paidAmount', 'balance'));
    }

    public function attendance()
    {
        $student = Auth::user();
        
        $attendance = Attendance::where('student_id', $student->id)
            ->with('schoolClass')
            ->latest()
            ->paginate(20);
            
        $stats = [
            'present' => Attendance::where('student_id', $student->id)
                ->where('status', 'present')
                ->count(),
            'absent' => Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->count(),
            'total' => Attendance::where('student_id', $student->id)->count()
        ];
        
        return view('student.attendance.index', compact('attendance', 'stats'));
    }
}
