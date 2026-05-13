<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::with(['profile', 'assignedClass', 'subjects'])
            ->where('role', 'student')
            ->latest()
            ->paginate(20);

        return view('prefect.students.index', compact('students'));
    }

    public function show(User $student)
    {
        abort_unless($student->role === 'student', 404);

        $student->load(['profile', 'assignedClass', 'subjects']);

        return view('prefect.students.show', compact('student'));
    }

    public function edit(User $student)
    {
        abort_unless($student->role === 'student', 404);

        return view('prefect.students.edit', [
            'student' => $student,
            'classes' => SchoolClass::active()->orderBy('level')->orderBy('stream')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $student)
    {
        abort_unless($student->role === 'student', 404);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $student->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'school_class_id' => $validated['school_class_id'] ?? null,
        ]);

        $student->subjects()->sync($validated['subject_ids'] ?? []);

        return redirect()->route('prefect.students.show', $student)->with('success', 'Student updated.');
    }
}
