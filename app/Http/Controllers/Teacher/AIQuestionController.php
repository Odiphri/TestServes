<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

class AIQuestionController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return view('teacher.ai-questions.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'number_of_questions' => 'required|integer|min:1|max:20',
            'difficulty' => 'required|in:easy,medium,hard',
            'exam_id' => 'required|exists:exams,id'
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $this->ensureTeacherOwnsExam($exam);

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
            foreach ($questions as $questionData) {
                $question = Question::create([
                    'exam_id' => $request->exam_id,
                    'question_text' => $questionData['question_text'],
                    'option_a' => $questionData['option_a'],
                    'option_b' => $questionData['option_b'],
                    'option_c' => $questionData['option_c'],
                    'option_d' => $questionData['option_d'],
                    'correct_answer' => $questionData['correct_answer'],
                    'points' => $questionData['points'],
                    'is_ai_generated' => true,
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

    public function getExamQuestions($examId)
    {
        $exam = Exam::findOrFail($examId);
        $this->ensureTeacherOwnsExam($exam);

        $questions = Question::where('exam_id', $examId)->get();

        return response()->json([
            'questions' => $questions,
            'exam' => $exam
        ]);
    }

    private function ensureTeacherOwnsExam(Exam $exam): void
    {
        abort_unless((int) $exam->created_by === (int) Auth::id(), 403);
    }
}
