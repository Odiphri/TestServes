<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Override;
use App\Models\User;
use Illuminate\Http\Request;

class OverrideController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeOverride($request);

        $overrides = Override::with(['student.assignedClass', 'exam', 'approver'])
            ->latest()
            ->paginate(20);
        $students = User::with('assignedClass')
            ->whereIn('role', ['student', 'prefect'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(500)
            ->get();
        $exams = Exam::with('schoolClass')
            ->latest()
            ->limit(500)
            ->get();

        $routePrefix = $this->routePrefix($request);

        return view('hod.overrides.index', compact('overrides', 'students', 'exams', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $this->authorizeOverride($request);

        $validated = $request->validate([
            'student_portal_id' => ['required', 'string', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'exam_id' => ['nullable', 'exists:exams,id'],
            'reason' => ['required', 'string', 'max:1000'],
            'expiry_date' => ['required', 'date', 'after:now'],
        ]);

        $student = $this->findStudent($validated['student_portal_id'], $validated['student_name']);

        if (! $student) {
            return back()
                ->withInput()
                ->withErrors(['student_portal_id' => 'No student matched both that ID and name.']);
        }

        Override::updateOrCreate(
            [
                'student_id' => $student->id,
                'exam_id' => $validated['exam_id'] ?? null,
            ],
            [
                'approved_by' => $request->user()->id,
                'reason' => $validated['reason'],
                'expiry_date' => $validated['expiry_date'],
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Override created successfully.');
    }

    public function destroy(Request $request, Override $override)
    {
        $this->authorizeOverride($request);

        $override->delete();

        return back()->with('success', 'Override deleted successfully.');
    }

    private function findStudent(string $portalId, string $name): ?User
    {
        $portalId = trim($portalId);
        $name = trim($name);

        $student = User::whereIn('role', ['student', 'prefect'])
            ->where('portal_id', $portalId)
            ->first();

        if (! $student) {
            return null;
        }

        $submittedName = strtolower($name);
        $fullName = strtolower(trim($student->first_name . ' ' . $student->last_name));
        $reversedName = strtolower(trim($student->last_name . ' ' . $student->first_name));

        return str_contains($fullName, $submittedName) || str_contains($reversedName, $submittedName)
            ? $student
            : null;
    }

    private function authorizeOverride(Request $request): void
    {
        abort_unless($request->user()?->canOverrideExamAccess(), 403);
    }

    private function routePrefix(Request $request): string
    {
        $routeName = (string) $request->route()->getName();

        return match (true) {
            str_starts_with($routeName, 'admin.') => 'admin',
            str_starts_with($routeName, 'cbt.') => 'cbt',
            str_starts_with($routeName, 'teacher.') => 'teacher',
            default => 'hod',
        };
    }
}
