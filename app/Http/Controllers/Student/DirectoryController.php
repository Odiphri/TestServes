<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;

class DirectoryController extends Controller
{
    public function students()
    {
        $students = User::with(['profile', 'assignedClass'])
            ->where('role', 'student')
            ->orderBy('last_name')
            ->paginate(20);

        return view('student.directory.students', compact('students'));
    }

    public function student(User $student)
    {
        abort_unless($student->role === 'student', 404);

        $student->load(['profile', 'assignedClass', 'subjects']);

        return view('student.directory.student', compact('student'));
    }
}
