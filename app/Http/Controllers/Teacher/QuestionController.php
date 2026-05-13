<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return redirect()
            ->route('teacher.exams')
            ->with('info', 'Questions are managed from each exam.');
    }

    public function create()
    {
        return redirect()
            ->route('teacher.exams')
            ->with('info', 'Open an exam to add questions.');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('teacher.exams')
            ->with('info', 'Open an exam to add questions.');
    }

    public function edit(Question $question)
    {
        return redirect()
            ->route('teacher.exams.edit', $question->exam_id)
            ->with('info', 'Edit questions from the exam editor.');
    }

    public function update(Request $request, Question $question)
    {
        return redirect()
            ->route('teacher.exams.edit', $question->exam_id)
            ->with('info', 'Edit questions from the exam editor.');
    }

    public function destroy(Question $question)
    {
        $examId = $question->exam_id;
        $question->delete();

        return redirect()
            ->route('teacher.exams.edit', $examId)
            ->with('success', 'Question deleted.');
    }
}
