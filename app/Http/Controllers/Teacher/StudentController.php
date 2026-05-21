<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->can('students.manage')) {
            return app(UserManagementController::class)->students($request);
        }

        $teacher = Auth::user();
        $classIds = $teacher->teachingClasses()
            ->pluck('school_classes.id')
            ->merge($teacher->assignedClasses()->pluck('school_classes.id'))
            ->unique()
            ->values();

        $students = User::with(['profile', 'assignedClass', 'subjects'])
            ->where('role', 'student')
            ->when($classIds->isNotEmpty(), fn ($query) => $query->whereIn('school_class_id', $classIds))
            ->latest()
            ->paginate(20);

        return view('teacher.students.index', compact('students'));
    }
}
