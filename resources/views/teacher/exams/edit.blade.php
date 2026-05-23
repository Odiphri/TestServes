@extends('layouts.admin')

@section('title', $exam->title)

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<div class="exam-builder">
    <div class="d-flex align-items-start gap-3 mb-3">
        <a href="{{ route(($routePrefix ?? 'teacher') . '.exams') }}" class="btn btn-light builder-back" title="Back to exams">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-1">{{ $exam->title }}</h2>
            <p class="text-muted mb-2">
                Class: {{ $exam->target_class_names ?: ($exam->schoolClass->full_name ?? 'N/A') }} | Duration: {{ $exam->duration_minutes }} mins
            </p>
            @if($exam->shuffle_questions)
                <span class="mini-badge"><i class="fas fa-random me-1"></i>Shuffle On</span>
            @endif
        </div>
    </div>

    <div class="builder-toolbar mb-4">
        <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.toggle-live', $exam) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn {{ $exam->is_live ? 'btn-secondary' : 'btn-success' }}">
                <i class="fas fa-broadcast-tower me-2"></i>{{ $exam->is_live ? 'Unpublish Exam' : 'Publish Exam' }}
            </button>
        </form>
        <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#settingsPanel">
            <i class="fas fa-cog me-2"></i>Settings
        </button>
        <button class="btn btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#aiPanel">
            <i class="fas fa-magic me-2"></i>AI Set Questions
        </button>
        <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.destroy', $exam) }}" class="d-inline" onsubmit="return confirm('Delete this exam and all its questions?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="far fa-trash-alt me-2"></i>Delete Exam
            </button>
        </form>
        <button class="btn btn-primary-custom" type="button" data-bs-toggle="collapse" data-bs-target="#manualQuestionPanel">
            <i class="fas fa-plus me-2"></i>Add Question
        </button>
    </div>

    <div class="collapse mb-4" id="settingsPanel">
        <div class="builder-panel">
            <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.update', $exam) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="title">Exam Title</label>
                        <input class="form-control" id="title" name="title" value="{{ old('title', $exam->title) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="duration_minutes">Duration</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1" max="300" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="is_live">Published</label>
                        <select class="form-select" id="is_live" name="is_live">
                            <option value="0" @selected(!$exam->is_live)>Offline</option>
                            <option value="1" @selected($exam->is_live)>Live</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="start_time">Start Time</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="{{ $exam->start_time ? $exam->start_time->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="end_time">End Time</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="{{ $exam->end_time ? $exam->end_time->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" @checked($exam->shuffle_questions)>
                            <label class="form-check-label" for="shuffle_questions">Shuffle questions</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show_results" name="show_results" value="1" @checked($exam->show_results)>
                            <label class="form-check-label" for="show_results">Results visible to students</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary-custom mt-3" type="submit">Save Settings</button>
            </form>
        </div>
    </div>

    <div class="collapse mb-4" id="aiPanel">
        <div class="builder-panel">
            <form id="aiQuestionForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="topic">Topic</label>
                        <input class="form-control" id="topic" name="topic" placeholder="e.g. Algebra, Comprehension" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="number_of_questions">Questions</label>
                        <input type="number" class="form-control" id="number_of_questions" name="number_of_questions" min="1" max="20" value="5" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="points_per_question">Points Each</label>
                        <input type="number" class="form-control" id="points_per_question" name="points_per_question" min="1" max="100" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="overall_points">Overall Points</label>
                        <input type="number" class="form-control" id="overall_points" name="overall_points" min="1" max="2000" value="5" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="difficulty">Difficulty</label>
                        <select class="form-select" id="difficulty" name="difficulty" required>
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100" id="generateBtn">
                            <i class="fas fa-magic me-2"></i>Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="collapse mb-4 {{ $exam->questions->isEmpty() ? 'show' : '' }}" id="manualQuestionPanel">
        <div class="builder-panel">
            <form id="manualQuestionForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" for="question_text">Question</label>
                    <input type="hidden" id="question_text" name="question_text">
                    <div id="questionEditorToolbar">
                        <span class="ql-formats">
                            <button class="ql-bold" type="button"></button>
                            <button class="ql-italic" type="button"></button>
                            <button class="ql-underline" type="button"></button>
                            <button class="ql-strike" type="button"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-script" value="sub" type="button"></button>
                            <button class="ql-script" value="super" type="button"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered" type="button"></button>
                            <button class="ql-list" value="bullet" type="button"></button>
                            <button class="ql-blockquote" type="button"></button>
                            <button class="ql-code-block" type="button"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean" type="button"></button>
                        </span>
                    </div>
                    <div id="questionEditor" class="rich-question-editor"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="question_image">Question Image</label>
                    <input type="file" class="form-control" id="question_image" name="question_image" accept="image/png,image/jpeg,image/webp">
                </div>
                <div class="row g-3">
                    @foreach(['a', 'b', 'c', 'd'] as $letter)
                        <div class="col-md-6">
                            <label class="form-label" for="option_{{ $letter }}">Option {{ strtoupper($letter) }}</label>
                            <input class="form-control" id="option_{{ $letter }}" name="option_{{ $letter }}" required>
                        </div>
                    @endforeach
                    <div class="col-md-6">
                        <label class="form-label" for="correct_answer">Correct Answer</label>
                        <select class="form-select" id="correct_answer" name="correct_answer" required>
                            <option value="">Select answer</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="points">Points</label>
                        <input type="number" class="form-control" id="points" name="points" min="1" value="1" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary-custom mt-3" id="addQuestionBtn">
                    <i class="fas fa-plus me-2"></i>Add Question
                </button>
            </form>
        </div>
    </div>

    <div class="questions-board">
        @if($exam->questions->isEmpty())
            <div class="empty-questions">
                <p>No questions have been added to this exam yet.</p>
                <button class="btn btn-primary-custom" type="button" data-bs-toggle="collapse" data-bs-target="#manualQuestionPanel">
                    Add First Question
                </button>
                <button class="btn btn-ai ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#aiPanel">
                    <i class="fas fa-magic me-2"></i>AI Generate
                </button>
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Questions</h5>
                <span class="badge bg-primary">{{ $exam->questions->count() }} total</span>
            </div>
            @foreach($exam->questions as $index => $question)
                <div class="question-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="question-title">Question {{ $index + 1 }}</div>
                            <div class="question-content mb-2">{!! $question->question_text !!}</div>
                        </div>
                        <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exam-questions.delete', $question) }}" onsubmit="return deleteQuestion(event, this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete question">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                    @if($question->image_path)
                        <img class="question-image" src="{{ asset('storage/' . $question->image_path) }}" alt="Question image">
                    @endif
                    <div class="row g-2 mt-2">
                        @foreach($question->options as $key => $option)
                            <div class="col-md-6">
                                <div class="option-box {{ $question->correct_answer === $key ? 'is-correct' : '' }}">
                                    <strong>{{ $key }}.</strong> {{ $option }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<style>
.exam-builder {
    color: #0a1931;
}

.builder-back {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(10, 25, 49, .12);
}

.mini-badge {
    display: inline-flex;
    align-items: center;
    border: 1px solid #bed4ef;
    background: #eaf3ff;
    color: #29558c;
    border-radius: 6px;
    padding: 3px 10px;
    font-size: .8rem;
    font-weight: 700;
}

.builder-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.btn-ai {
    border: 1px solid #e2bdec;
    background: #fff;
    color: #8a1aa1;
}

.builder-panel,
.questions-board {
    background: #fff;
    border: 1px solid #dde4ed;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(10, 25, 49, .08);
}

.empty-questions {
    min-height: 175px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
}

.empty-questions p {
    flex: 0 0 100%;
    color: #667381;
    margin-bottom: 8px;
}

.question-item {
    border: 1px solid #e4eaf1;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 14px;
    background: #fbfcfe;
}

.question-title {
    font-weight: 700;
    margin-bottom: 6px;
}

.rich-question-editor {
    min-height: 150px;
    background: #fff;
}

#questionEditorToolbar {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.ql-container {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    font-size: 1rem;
}

.question-content p,
.question-content ul,
.question-content ol,
.question-content blockquote,
.question-content pre {
    margin-bottom: .65rem;
}

.question-content :last-child {
    margin-bottom: 0;
}

.question-image {
    display: block;
    max-width: min(520px, 100%);
    max-height: 260px;
    object-fit: contain;
    border: 1px solid #e4eaf1;
    border-radius: 8px;
    background: #fff;
    margin: 8px 0;
}

.option-box {
    border: 1px solid #e4eaf1;
    border-radius: 6px;
    padding: 10px 12px;
    background: #fff;
}

.option-box.is-correct {
    border-color: #24a148;
    color: #137333;
    background: #eefaf2;
    font-weight: 700;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const jsonRequestHeaders = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
};
const formRequestHeaders = {
    'X-CSRF-TOKEN': csrfToken,
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
};
const questionEditor = new Quill('#questionEditor', {
    theme: 'snow',
    modules: {
        toolbar: '#questionEditorToolbar'
    },
    placeholder: 'Type the question here...'
});

document.getElementById('manualQuestionForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const button = document.getElementById('addQuestionBtn');
    const questionText = document.getElementById('question_text');
    const plainQuestionText = questionEditor.getText().trim();

    if (!plainQuestionText) {
        showAlert('Please enter the question text.', 'danger');
        questionEditor.focus();
        return;
    }

    questionText.value = questionEditor.root.innerHTML;
    const formData = new FormData(this);

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';

    fetch('{{ route(($routePrefix ?? 'teacher') . '.exams.add-question', $exam) }}', {
        method: 'POST',
        headers: formRequestHeaders,
        credentials: 'same-origin',
        body: formData
    })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Error adding question');
            }
            return data;
        })
        .then(data => {
            showAlert(data.message, 'success');
            questionEditor.setContents([]);
            window.location.reload();
        })
        .catch(error => showAlert(error.message, 'danger'))
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-plus me-2"></i>Add Question';
        });
});

document.getElementById('aiQuestionForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const button = document.getElementById('generateBtn');
    const formData = new FormData(this);

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';

    fetch('{{ route(($routePrefix ?? 'teacher') . '.exams.generate-questions', $exam) }}', {
        method: 'POST',
        headers: jsonRequestHeaders,
        credentials: 'same-origin',
        body: JSON.stringify({
            topic: formData.get('topic'),
            number_of_questions: formData.get('number_of_questions'),
            points_per_question: formData.get('points_per_question'),
            overall_points: formData.get('overall_points'),
            difficulty: formData.get('difficulty')
        })
    })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Error generating questions');
            }
            return data;
        })
        .then(data => {
            showAlert(data.message, 'success');
            window.location.reload();
        })
        .catch(error => showAlert(error.message, 'danger'))
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-magic me-2"></i>Generate';
        });
});

function deleteQuestion(event, form) {
    event.preventDefault();
    if (!confirm('Delete this question?')) {
        return false;
    }

    fetch(form.action, {
        method: 'POST',
        headers: formRequestHeaders,
        credentials: 'same-origin',
        body: new FormData(form)
    })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Error deleting question');
            }
            return data;
        })
        .then(data => {
            showAlert(data.message, 'success');
            window.location.reload();
        })
        .catch(error => showAlert(error.message, 'danger'));

    return false;
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

function syncAiOverallPoints() {
    const questionCount = document.getElementById('number_of_questions');
    const pointsEach = document.getElementById('points_per_question');
    const overallPoints = document.getElementById('overall_points');

    if (!questionCount || !pointsEach || !overallPoints) {
        return;
    }

    overallPoints.value = Math.max(1, Number(questionCount.value || 0) * Number(pointsEach.value || 0));
}

document.getElementById('number_of_questions')?.addEventListener('input', syncAiOverallPoints);
document.getElementById('points_per_question')?.addEventListener('input', syncAiOverallPoints);
syncAiOverallPoints();
</script>
@endsection
