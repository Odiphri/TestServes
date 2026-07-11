<?php

namespace App\Http\Controllers\CBT;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function __construct(private AIService $aiService)
    {
    }

    public function index(Request $request)
    {
        $exams = Exam::with(['subject', 'schoolClass', 'creator'])
            ->withCount(['questions', 'attempts'])
            ->withAvg('attempts', 'percentage')
            ->when($request->filled('search'), fn ($query) => $this->applyExamSearch($query, (string) $request->query('search')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $routePrefix = $this->routePrefix();
        $search = $request->query('search');

        return view('teacher.exams.index', compact('exams', 'routePrefix', 'search'));
    }

    public function create()
    {
        return view('teacher.exams.create', [
            'classes' => SchoolClass::active()->orderBy('level')->orderBy('stream')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateExam($request);
        $this->ensureSubjectBelongsToClass((int) $validated['subject_id'], (int) $validated['school_class_id']);
        $validated['target_class_ids'] = $this->targetClassIds($validated);
        $validated['created_by'] = Auth::id();
        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['show_results'] = $request->boolean('show_results');
        $validated['is_live'] = $request->boolean('is_live');
        $validated['pass_mark'] = $validated['pass_mark'] ?? SchoolSetting::current()->pass_mark;

        if ($validated['is_live'] && (empty($validated['end_time']) || now()->gte($validated['end_time']))) {
            $validated['start_time'] = now();
            $validated['end_time'] = now()->addMinutes((int) $validated['duration_minutes']);
        }

        $exam = Exam::create($validated);

        return redirect()->route($this->routePrefix() . '.exams.show', $exam)->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['subject', 'schoolClass', 'creator', 'questions']);

        $routePrefix = $this->routePrefix();

        return view('teacher.exams.edit', compact('exam', 'routePrefix'));
    }

    public function edit(Exam $exam)
    {
        $exam->load(['subject', 'schoolClass', 'creator', 'questions']);
        $routePrefix = $this->routePrefix();

        return view('teacher.exams.edit', compact('exam', 'routePrefix'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
            'is_live' => 'boolean',
        ]);

        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['show_results'] = $request->boolean('show_results');
        $validated['is_live'] = $request->boolean('is_live');

        if ($validated['is_live'] && (empty($validated['end_time']) || now()->gte($validated['end_time']))) {
            $validated['start_time'] = now();
            $validated['end_time'] = now()->addMinutes((int) $validated['duration_minutes']);
        }

        $exam->update($validated);

        return redirect()->route($this->routePrefix() . '.exams.show', $exam)->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        foreach ($exam->questions as $question) {
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
        }

        $exam->delete();

        return redirect()->route($this->routePrefix() . '.exams')->with('success', 'Exam deleted successfully.');
    }

    public function toggleLive(Exam $exam)
    {
        $payload = ['is_live' => !$exam->is_live];

        if (!$exam->is_live && (!$exam->end_time || $exam->end_time->lt(now()))) {
            $payload['start_time'] = now();
            $payload['end_time'] = now()->addMinutes($exam->duration_minutes);
        }

        $exam->update($payload);

        return back()->with('success', $exam->is_live ? 'Exam is now live.' : 'Exam is now offline.');
    }

    public function toggleResults(Exam $exam)
    {
        $exam->update(['show_results' => ! $exam->show_results]);

        return back()->with('success', $exam->show_results ? 'Results are visible.' : 'Results are hidden.');
    }

    public function generateQuestions(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'number_of_questions' => 'required|integer|min:1|max:20',
            'points_per_question' => 'required|integer|min:1|max:100',
            'overall_points' => 'required|integer|min:1|max:2000',
            'difficulty' => 'nullable|in:easy,medium,hard',
        ]);

        $expectedGeneratedPoints = (int) $validated['number_of_questions'] * (int) $validated['points_per_question'];

        if ((int) $validated['overall_points'] !== $expectedGeneratedPoints) {
            return response()->json([
                'success' => false,
                'message' => "Overall points must equal number of questions x points per question ({$expectedGeneratedPoints}).",
            ], 422);
        }

        try {
            $questions = $this->aiService->generateQuestions(
                $validated['topic'],
                (int) $validated['number_of_questions'],
                $validated['difficulty'] ?? 'medium',
                (int) $validated['points_per_question'],
                (int) $validated['overall_points']
            );

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate questions. Please try again.',
                ], 500);
            }

            $createdQuestions = [];
            $currentOrder = (int) $exam->questions()->max('order');

            foreach ($questions as $questionData) {
                $createdQuestions[] = Question::create([
                    'exam_id' => $exam->id,
                    'question_text' => $questionData['question_text'],
                    'option_a' => $questionData['option_a'],
                    'option_b' => $questionData['option_b'],
                    'option_c' => $questionData['option_c'],
                    'option_d' => $questionData['option_d'],
                    'correct_answer' => $questionData['correct_answer'],
                    'points' => $questionData['points'],
                    'is_ai_generated' => true,
                    'order' => ++$currentOrder,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully generated ' . count($createdQuestions) . ' questions!',
                'questions' => $createdQuestions,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating questions: ' . $exception->getMessage(),
            ], 500);
        }
    }

    public function addManualQuestion(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:A,B,C,D',
            'points' => 'required|integer|min:1',
            'question_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $request->hasFile('question_image')
            ? $request->file('question_image')->store('question-images', 'public')
            : null;

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => $this->sanitizeQuestionHtml($validated['question_text']),
            'option_a' => $validated['option_a'],
            'option_b' => $validated['option_b'],
            'option_c' => $validated['option_c'],
            'option_d' => $validated['option_d'],
            'correct_answer' => $validated['correct_answer'],
            'image_path' => $imagePath,
            'points' => $validated['points'],
            'is_ai_generated' => false,
            'order' => ((int) $exam->questions()->max('order')) + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question added successfully!',
            'question' => $question,
        ]);
    }

    public function deleteQuestion(Question $question)
    {
        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully!',
        ]);
    }

    private function validateExam(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'exists:school_classes,id',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'pass_mark' => 'nullable|integer|min:0|max:100',
        ]);
    }

    private function routePrefix(): string
    {
        return str_starts_with((string) request()->route()->getName(), 'hod.') ? 'hod' : 'cbt';
    }

    private function ensureSubjectBelongsToClass(int $subjectId, int $classId): void
    {
        abort_unless(
            Subject::where('id', $subjectId)->where('school_class_id', $classId)->exists(),
            422,
            'The selected subject does not belong to the selected class.'
        );
    }

    private function targetClassIds(array $validated): array
    {
        $baseClass = SchoolClass::findOrFail($validated['school_class_id']);
        $targetClassIds = collect($validated['target_class_ids'] ?? [$baseClass->id])
            ->push($baseClass->id)
            ->filter()
            ->map(fn ($classId) => (int) $classId)
            ->unique()
            ->values();

        $validCount = SchoolClass::whereIn('id', $targetClassIds)
            ->where('level', $baseClass->level)
            ->count();

        abort_unless($validCount === $targetClassIds->count(), 422, 'Exam target classes must be in the selected class level.');

        return $targetClassIds->all();
    }

    private function sanitizeQuestionHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><ol><ul><li><blockquote><code><pre><sub><sup><span>';
        $cleanHtml = strip_tags($html, $allowedTags);

        if (! class_exists(\DOMDocument::class)) {
            return $cleanHtml;
        }

        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<div>' . $cleanHtml . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        foreach ($document->getElementsByTagName('*') as $node) {
            while ($node->attributes && $node->attributes->length > 0) {
                $node->removeAttributeNode($node->attributes->item(0));
            }
        }

        $wrapper = $document->getElementsByTagName('div')->item(0);
        $output = '';

        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $output .= $document->saveHTML($child);
            }
        }

        return trim($output);
    }

    private function applyExamSearch($query, string $search): void
    {
        $search = strtolower(trim($search));

        $query->where(function ($query) use ($search) {
            $query->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                ->orWhereHas('subject', fn ($query) => $query->whereRaw('LOWER(name) like ?', ["%{$search}%"]))
                ->orWhereHas('schoolClass', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(level) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(stream) like ?', ["%{$search}%"]);
                })
                ->orWhereHas('creator', function ($query) use ($search) {
                    $fullNameExpression = "LOWER(CONCAT(first_name, ' ', last_name))";

                    $query->whereRaw('LOWER(first_name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) like ?', ["%{$search}%"])
                        ->orWhereRaw($fullNameExpression . ' like ?', ["%{$search}%"]);
                });
        });
    }
}
