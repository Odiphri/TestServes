<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        return view('management.academics.subjects', [
            'subjects' => $subjectsQuery->latest()->paginate(20)->withQueryString(),
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
            'code' => ['required', 'string', 'max:255', 'unique:subjects,code'],
            'section' => ['required', Rule::in(['jss', 'sss'])],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->ensureClassMatchesSection((int) $validated['school_class_id'], $validated['section']);

        Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'school_class_id' => $validated['school_class_id'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Subject created successfully.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:subjects,code,' . $subject->id],
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
}
