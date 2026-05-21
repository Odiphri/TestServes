<?php

namespace App\Http\Controllers;

use App\Models\StudentRole;
use Illuminate\Http\Request;

class StudentRoleController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureViewer($request);

        return view('management.student-roles.index', [
            'studentRoles' => StudentRole::withCount('students')->latest()->paginate(20),
            'canEdit' => $this->canEditRoles($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:student_roles,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        StudentRole::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Student role created successfully.');
    }

    public function update(Request $request, StudentRole $studentRole)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:student_roles,name,' . $studentRole->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $studentRole->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Student role updated successfully.');
    }

    private function ensureViewer(Request $request): void
    {
        abort_unless($request->user() && in_array($request->user()->role, ['admin', 'hod', 'teacher', 'cbt_personnel'], true), 403);
    }

    private function ensureEditor(Request $request): void
    {
        abort_unless($request->user() && $this->canEditRoles($request), 403);
    }

    private function canEditRoles(Request $request): bool
    {
        $user = $request->user();

        return $user && (in_array($user->role, ['admin', 'hod'], true) || $user->can('student_roles.manage'));
    }
}
