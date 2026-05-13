<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();

        $classes = SchoolClass::with(['subjects', 'classTeacher'])
            ->where('class_teacher_id', $teacher->id)
            ->orWhereHas('teachers', fn ($query) => $query->where('users.id', $teacher->id))
            ->orWhereHas('assignedStaff', fn ($query) => $query->where('users.id', $teacher->id))
            ->latest()
            ->paginate(20);

        return view('teacher.classes.index', compact('classes'));
    }
}
