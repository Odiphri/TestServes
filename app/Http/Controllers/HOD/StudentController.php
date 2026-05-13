<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\User;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::with(['profile', 'assignedClass', 'subjects'])
            ->where('role', 'student')
            ->latest()
            ->paginate(20);

        return view('hod.students.index', compact('students'));
    }
}
