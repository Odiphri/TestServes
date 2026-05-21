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

        $student = $this->findStudentByPortalId($validated['student_portal_id']);

        if (! $student) {
            return back()
                ->withInput()
                ->withErrors(['student_portal_id' => 'No student was found with that login ID.']);
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

    private function findStudentByPortalId(string $portalId): ?User
    {
        return User::whereIn('role', ['student', 'prefect'])
            ->where('portal_id', trim($portalId))
            ->first();
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
