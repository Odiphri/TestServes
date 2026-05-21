<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Question;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $teacher = Auth::user();

        $exams = Exam::with(['subject', 'schoolClass'])
            ->withCount(['questions', 'attempts'])
            ->withAvg('attempts', 'percentage')
            ->where('created_by', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.exams.index', compact('exams'));
    }

    public function create()
    {
        $teacher = Auth::user();
        
        $classes = $teacher->teachingClasses()
            ->select('school_classes.*')
            ->distinct()
            ->get()
            ->merge(SchoolClass::where('class_teacher_id', $teacher->id)->get())
            ->unique('id')
            ->values();

        $targetClassLevels = $classes->pluck('level')->unique()->values();
        $targetClasses = SchoolClass::active()
            ->whereIn('level', $targetClassLevels)
            ->orderBy('level')
            ->orderBy('stream')
            ->get();

        $subjects = $teacher->teachingSubjects()
            ->select('subjects.*')
            ->distinct()
            ->get();

        return view('teacher.exams.create', compact('classes', 'subjects', 'targetClasses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'exists:school_classes,id',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
            'is_live' => 'boolean',
        ]);

        $this->ensureTeacherCanSetSubject((int) $request->subject_id, (int) $request->school_class_id);
        $this->ensureSubjectBelongsToClass((int) $request->subject_id, (int) $request->school_class_id);

        $payload = [
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'school_class_id' => $request->school_class_id,
            'target_class_ids' => $this->targetClassIds((int) $request->school_class_id, $request->input('target_class_ids', [])),
            'duration_minutes' => $request->duration_minutes,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'show_results' => $request->boolean('show_results', true),
            'is_live' => $request->boolean('is_live'),
            'pass_mark' => SchoolSetting::current()->pass_mark,
            'created_by' => Auth::id(),
        ];

        if ($payload['is_live'] && (empty($payload['end_time']) || now()->gte($payload['end_time']))) {
            $payload['start_time'] = now();
            $payload['end_time'] = now()->addMinutes((int) $payload['duration_minutes']);
        }

        $exam = Exam::create($payload);

        return redirect()->route('teacher.exams.show', $exam->id)
            ->with('success', 'Exam created successfully! Now add questions.');
    }

    public function show(Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $exam->load(['questions', 'subject', 'schoolClass']);

        return view('teacher.exams.edit', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);
        
        $exam->load(['questions', 'subject', 'schoolClass']);
        
        return view('teacher.exams.edit', compact('exam'));
    }

    public function generateQuestions(Request $request, Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $request->validate([
            'topic' => 'required|string|max:255',
            'number_of_questions' => 'required|integer|min:1|max:20',
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        try {
            $questions = $this->aiService->generateQuestions(
                $request->topic,
                $request->number_of_questions,
                $request->difficulty
            );

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate questions. Please try again.'
                ], 500);
            }

            // Store generated questions in database
            $createdQuestions = [];
            $currentOrder = (int) $exam->questions()->max('order');
            foreach ($questions as $questionData) {
                $question = Question::create([
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
                
                $createdQuestions[] = $question;
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully generated ' . count($createdQuestions) . ' questions!',
                'questions' => $createdQuestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating questions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addManualQuestion(Request $request, Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $request->validate([
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
            'question_text' => $this->sanitizeQuestionHtml($request->question_text),
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_answer' => $request->correct_answer,
            'image_path' => $imagePath,
            'points' => $request->points,
            'is_ai_generated' => false,
            'order' => ((int) $exam->questions()->max('order')) + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question added successfully!',
            'question' => $question
        ]);
    }

    public function deleteQuestion(Question $question)
    {
        $this->ensureTeacherOwnsExam($question->exam);

        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }
        
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully!'
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
            'is_live' => 'boolean',
        ]);

        $payload = [
            'title' => $request->title,
            'duration_minutes' => $request->duration_minutes,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'show_results' => $request->boolean('show_results'),
            'is_live' => $request->boolean('is_live'),
        ];

        if ($payload['is_live'] && (empty($payload['end_time']) || now()->gte($payload['end_time']))) {
            $payload['start_time'] = now();
            $payload['end_time'] = now()->addMinutes((int) $payload['duration_minutes']);
        }

        $exam->update($payload);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Exam updated successfully!');
    }

    public function toggleLive(Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $payload = ['is_live' => ! $exam->is_live];

        if (!$exam->is_live && (!$exam->end_time || $exam->end_time->lt(now()))) {
            $payload['start_time'] = now();
            $payload['end_time'] = now()->addMinutes($exam->duration_minutes);
        }

        $exam->update($payload);

        return back()->with('success', $exam->is_live ? 'Exam published.' : 'Exam moved offline.');
    }

    public function toggleResults(Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        $exam->update(['show_results' => ! $exam->show_results]);

        return back()->with('success', $exam->show_results ? 'Results are visible.' : 'Results are hidden.');
    }

    public function destroy(Exam $exam)
    {
        $this->ensureTeacherOwnsExam($exam);

        foreach ($exam->questions as $question) {
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
        }

        $exam->delete();

        return redirect()->route('teacher.exams')->with('success', 'Exam deleted successfully.');
    }

    private function ensureTeacherOwnsExam(Exam $exam): void
    {
        abort_unless((int) $exam->created_by === (int) Auth::id(), 403);
    }

    private function ensureTeacherCanSetSubject(int $subjectId, int $classId): void
    {
        $teacher = Auth::user();

        $isAssigned = $teacher->teachingSubjects()
            ->where('subjects.id', $subjectId)
            ->wherePivot('school_class_id', $classId)
            ->exists();

        abort_unless($isAssigned, 403, 'You can only create exams for subjects assigned to you.');
    }

    private function ensureSubjectBelongsToClass(int $subjectId, int $classId): void
    {
        abort_unless(
            Subject::where('id', $subjectId)->where('school_class_id', $classId)->exists(),
            422,
            'The selected subject does not belong to the selected class.'
        );
    }

    private function targetClassIds(int $baseClassId, array $targetClassIds): array
    {
        $baseClass = SchoolClass::findOrFail($baseClassId);
        $targetClassIds = collect($targetClassIds ?: [$baseClassId])
            ->push($baseClassId)
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

        if (!class_exists(\DOMDocument::class)) {
            return $cleanHtml;
        }

        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<div>'.$cleanHtml.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
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
}
