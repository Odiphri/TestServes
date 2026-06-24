<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with(['subject', 'attempts.student']);

        if (! $this->canViewAllResults()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('search')) {
            $this->applyExamSearch($query, (string) $request->query('search'));
        }

        $exams = $query->latest()->paginate(15)->withQueryString();
        $routePrefix = $this->routePrefix();
        $search = $request->query('search');

        return view('teacher.results.index', compact('exams', 'routePrefix', 'search'));
    }

    public function show(Exam $exam)
    {
        abort_unless($this->canAccessExamResults($exam), 403);

        $exam->load(['subject', 'attempts.student']);
        $routePrefix = $this->routePrefix();
        $canAllowRetakes = $this->canAllowRetake($exam);

        return view('teacher.results.show', compact('exam', 'routePrefix', 'canAllowRetakes'));
    }

    public function export(Exam $exam)
    {
        abort_unless($this->canAccessExamResults($exam), 403);

        $exam->load(['attempts.student']);

        $filename = 'exam-results-' . $exam->id . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($exam) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student', 'Score', 'Total Points', 'Percentage', 'Grade', 'Submitted At']);

            foreach ($exam->attempts as $attempt) {
                fputcsv($handle, [
                    $attempt->student->full_name ?? 'N/A',
                    $attempt->score,
                    $attempt->total_points,
                    $attempt->percentage,
                    $attempt->grade,
                    optional($attempt->submitted_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }

    public function allowRetake(Exam $exam, ExamAttempt $attempt)
    {
        abort_unless((int) $attempt->exam_id === (int) $exam->id, 404);
        abort_unless($this->canAllowRetake($exam), 403);

        $attempt->delete();

        return back()->with('success', 'Student can now retake this exam.');
    }

    private function canAllowRetake(Exam $exam): bool
    {
        $user = Auth::user();

        return $this->canAccessExamResults($exam)
            && ($user->role !== 'teacher' || (int) $exam->created_by === (int) $user->id || $user->can('exams.allow_retakes'));
    }

    private function canAccessExamResults(Exam $exam): bool
    {
        $user = Auth::user();

        if ($this->canViewAllResults()) {
            return true;
        }

        return $user->role === 'teacher' && (int) $exam->created_by === (int) $user->id;
    }

    private function canViewAllResults(): bool
    {
        $user = Auth::user();

        return in_array($user->role, ['admin', 'hod', 'cbt_personnel'], true)
            || $user->can('results.view_all');
    }

    private function routePrefix(): string
    {
        $routeName = request()->route()?->getName() ?? '';

        return match (true) {
            str_starts_with($routeName, 'hod.') => 'hod',
            str_starts_with($routeName, 'cbt.') => 'cbt',
            str_starts_with($routeName, 'admin.') => 'admin',
            default => 'teacher',
        };
    }

    private function applyExamSearch($query, string $search): void
    {
        $search = strtolower(trim($search));

        $query->where(function ($query) use ($search) {
            $query->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                ->orWhereHas('subject', fn ($query) => $query->whereRaw('LOWER(name) like ?', ["%{$search}%"]));
        });
    }
}
