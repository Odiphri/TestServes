<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Services\StudentPromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicSessionController extends Controller
{
    private array $terms = ['First Term', 'Second Term', 'Third Term'];

    public function index(Request $request)
    {
        return view('management.academics.sessions', [
            'sessions' => AcademicSession::latest()->paginate(15)->withQueryString(),
            'activeSession' => AcademicSession::active()->latest('activated_at')->first(),
            'terms' => $this->terms,
            'canManageSessions' => $this->canManageSessions($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureSessionManager($request);

        $validated = $this->validateSession($request);

        AcademicSession::create([
            ...$validated,
            'is_active' => false,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Academic session created successfully.');
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        $this->ensureSessionManager($request);

        $academicSession->update([
            ...$this->validateSession($request, $academicSession),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Academic session updated successfully.');
    }

    public function activate(Request $request, AcademicSession $academicSession, StudentPromotionService $promotionService)
    {
        $this->ensureSessionManager($request);

        $promotedCount = 0;

        DB::transaction(function () use ($academicSession, $request, $promotionService, &$promotedCount) {
            $wasAlreadyActive = $academicSession->is_active;

            AcademicSession::whereKeyNot($academicSession->id)->update(['is_active' => false]);

            if (!$wasAlreadyActive) {
                $promotedCount = $promotionService->promoteAllStudents();
            }

            $academicSession->update([
                'is_active' => true,
                'activated_at' => now(),
                'promoted_at' => $wasAlreadyActive ? $academicSession->promoted_at : now(),
                'promoted_students_count' => $wasAlreadyActive ? $academicSession->promoted_students_count : $promotedCount,
                'updated_by' => $request->user()->id,
            ]);
        });

        return back()->with(
            'success',
            "Academic session activated. {$promotedCount} student(s) were auto-promoted."
        );
    }

    private function validateSession(Request $request, ?AcademicSession $session = null): array
    {
        return $request->validate([
            'academic_year' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('academic_sessions', 'academic_year')
                    ->where(fn ($query) => $query->where('term', $request->input('term')))
                    ->ignore($session?->id),
            ],
            'term' => ['required', Rule::in($this->terms)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }

    private function ensureSessionManager(Request $request): void
    {
        abort_unless($this->canManageSessions($request), 403);
    }

    private function canManageSessions(Request $request): bool
    {
        return $request->user() && in_array($request->user()->role, ['admin', 'hod'], true);
    }
}
