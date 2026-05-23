<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicManagementController extends Controller
{
    private array $levels = ['JSS1', 'JSS2', 'JSS3', 'SS1', 'SS2', 'SS3'];
    private array $jssStreams = ['A', 'B', 'C'];
    private array $sssStreams = ['Science', 'Art', 'Commercial'];

    public function classes(Request $request)
    {
        $this->ensureEditor($request);

        $classesQuery = SchoolClass::with(['assignedStaff', 'subjects']);

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $classesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%")
                    ->orWhere('stream', 'like', "%{$search}%");
            });
        }

        return view('management.academics.classes', [
            'classes' => $classesQuery->latest()->paginate(20)->withQueryString(),
            'levels' => $this->levels,
            'jssStreams' => $this->jssStreams,
            'sssStreams' => $this->sssStreams,
            'streams' => array_merge($this->jssStreams, $this->sssStreams),
            'routePrefix' => $this->routePrefix($request),
            'search' => $request->query('search'),
        ]);
    }

    public function storeClass(Request $request)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', Rule::in($this->levels)],
            'stream' => ['nullable', Rule::in($this->allowedStreamsForLevel($request->input('level')))],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        SchoolClass::create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Class created successfully.');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', Rule::in($this->levels)],
            'stream' => ['nullable', Rule::in($this->allowedStreamsForLevel($request->input('level')))],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $class->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Class updated successfully.');
    }

    public function destroyClass(Request $request, SchoolClass $class)
    {
        $this->ensureEditor($request);

        DB::transaction(function () use ($class) {
            Exam::whereJsonContains('target_class_ids', $class->id)
                ->get()
                ->each(function (Exam $exam) use ($class) {
                    $targetClassIds = collect($exam->target_class_ids ?: [])
                        ->reject(fn ($classId) => (int) $classId === (int) $class->id)
                        ->values();

                    if ($targetClassIds->isEmpty() && (int) $exam->school_class_id !== (int) $class->id) {
                        $targetClassIds->push((int) $exam->school_class_id);
                    }

                    $exam->update(['target_class_ids' => $targetClassIds->all()]);
                });

            $class->delete();
        });

        return back()->with('success', 'Class deleted successfully.');
    }

    public function subjects(Request $request)
    {
        $this->ensureEditor($request);

        $subjectsQuery = Subject::with('schoolClass');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $subjectsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $subjectsQuery->where('school_class_id', $request->integer('class_id'));
        }

        $subjects = $subjectsQuery->latest()->get();
        $subjectGroups = $this->paginatedSubjectGroups($subjects, $request);

        return view('management.academics.subjects', [
            'subjectGroups' => $subjectGroups,
            'classes' => SchoolClass::active()->orderBy('level')->orderBy('stream')->get(),
            'routePrefix' => $this->routePrefix($request),
            'search' => $request->query('search'),
            'selectedClassId' => $request->query('class_id'),
        ]);
    }

    public function storeSubject(Request $request)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'section' => ['required', Rule::in(['jss', 'sss'])],
            'school_class_ids' => ['required', 'array', 'min:1'],
            'school_class_ids.*' => ['integer', 'distinct', 'exists:school_classes,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $classIds = array_map('intval', $validated['school_class_ids']);

        $this->ensureClassesMatchSection($classIds, $validated['section']);
        $this->ensureSubjectCodeIsAvailableForClasses($validated['code'], $classIds);

        DB::transaction(function () use ($request, $validated, $classIds) {
            foreach ($classIds as $classId) {
                Subject::create([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'school_class_id' => $classId,
                    'description' => $validated['description'] ?? null,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            }
        });

        return back()->with('success', count($classIds) . ' subject class offering(s) created successfully.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'code')
                    ->where(fn ($query) => $query->where('school_class_id', $request->input('school_class_id')))
                    ->ignore($subject->id),
            ],
            'section' => ['required', Rule::in(['jss', 'sss'])],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->ensureClassMatchesSection((int) $validated['school_class_id'], $validated['section']);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'school_class_id' => $validated['school_class_id'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Subject updated successfully.');
    }

    public function destroySubject(Request $request, Subject $subject)
    {
        $this->ensureEditor($request);

        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }

    public function destroySubjectGroup(Request $request)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'section' => ['required', Rule::in(['jss', 'sss'])],
        ]);

        $deletedCount = Subject::query()
            ->where('name', $validated['name'])
            ->where('code', $validated['code'])
            ->whereHas('schoolClass', function ($query) use ($validated) {
                $validated['section'] === 'jss'
                    ? $query->where('level', 'like', 'JSS%')
                    : $query->where('level', 'like', 'SS%');
            })
            ->delete();

        return back()->with(
            $deletedCount > 0 ? 'success' : 'error',
            $deletedCount > 0
                ? "{$validated['name']} was deleted from " . strtoupper($validated['section']) . " ({$deletedCount} class offering(s))."
                : 'No matching subject offerings were found to delete.'
        );
    }

    private function ensureEditor(Request $request): void
    {
        abort_unless($request->user() && in_array($request->user()->role, ['admin', 'hod'], true), 403);
    }

    private function routePrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()->getName(), 'hod.') ? 'hod' : 'admin';
    }

    private function ensureClassMatchesSection(int $classId, string $section): void
    {
        $class = SchoolClass::findOrFail($classId);
        $isJss = str_starts_with($class->level, 'JSS');

        abort_if($section === 'jss' && !$isJss, 422, 'JSS subjects must be attached to a JSS class.');
        abort_if($section === 'sss' && $isJss, 422, 'SSS subjects must be attached to an SS class.');
    }

    private function ensureClassesMatchSection(array $classIds, string $section): void
    {
        foreach ($classIds as $classId) {
            $this->ensureClassMatchesSection((int) $classId, $section);
        }
    }

    private function ensureSubjectCodeIsAvailableForClasses(string $code, array $classIds): void
    {
        $existingClassNames = Subject::query()
            ->with('schoolClass')
            ->where('code', $code)
            ->whereIn('school_class_id', $classIds)
            ->get()
            ->pluck('schoolClass.full_name')
            ->filter()
            ->join(', ');

        if ($existingClassNames !== '') {
            throw ValidationException::withMessages([
                'code' => "Subject code {$code} already exists for: {$existingClassNames}.",
            ]);
        }
    }

    private function allowedStreamsForLevel(?string $level): array
    {
        if ($level && str_starts_with($level, 'JSS')) {
            return $this->jssStreams;
        }

        if ($level && str_starts_with($level, 'SS')) {
            return $this->sssStreams;
        }

        return array_merge($this->jssStreams, $this->sssStreams);
    }

    private function paginatedSubjectGroups($subjects, Request $request): LengthAwarePaginator
    {
        $groups = $subjects
            ->groupBy(function (Subject $subject) {
                $section = str_starts_with($subject->schoolClass?->level ?? '', 'JSS') ? 'jss' : 'sss';

                return strtolower(trim($subject->name)) . '|' . strtolower(trim($subject->code)) . '|' . $section;
            })
            ->map(function ($group) {
                $first = $group->first();
                $section = str_starts_with($first->schoolClass?->level ?? '', 'JSS') ? 'jss' : 'sss';
                $classes = $group
                    ->pluck('schoolClass')
                    ->filter()
                    ->sortBy([['level', 'asc'], ['stream', 'asc']])
                    ->values();

                return (object) [
                    'name' => $first->name,
                    'code' => $first->code,
                    'section' => $section,
                    'subjects' => $group->sortBy(fn (Subject $subject) => $subject->schoolClass?->full_name ?? '')->values(),
                    'classes' => $classes,
                    'class_count' => $classes->count(),
                    'class_names' => $classes->pluck('full_name')->join(', '),
                    'active_count' => $group->where('is_active', true)->count(),
                    'inactive_count' => $group->where('is_active', false)->count(),
                    'all_active' => $group->every(fn (Subject $subject) => $subject->is_active),
                ];
            })
            ->sortBy('name')
            ->values();

        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $groups->forPage($currentPage, $perPage)->values(),
            $groups->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
