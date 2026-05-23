@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">AI Question Generator</h5>
                </div>
                <div class="card-body">
                    <!-- AI Generation Form -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form id="aiQuestionForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="exam_id" class="form-label">Select Exam</label>
                                            <select class="form-select" id="exam_id" name="exam_id" required>
                                                <option value="">Choose an exam...</option>
                                                @foreach($exams as $exam)
                                                    <option value="{{ $exam->id }}">
                                                        {{ $exam->title }} - {{ $exam->subject->name ?? 'No subject' }} - {{ $exam->schoolClass->full_name ?? 'No class' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="topic" class="form-label">Topic/Subject</label>
                                            <input type="text" class="form-control" id="topic" name="topic" 
                                                   placeholder="e.g., Photosynthesis, Algebra, World War II" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="number_of_questions" class="form-label">Number of Questions</label>
                                            <input type="number" class="form-control" id="number_of_questions" 
                                                   name="number_of_questions" min="1" max="20" value="5" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="difficulty" class="form-label">Difficulty Level</label>
                                            <select class="form-select" id="difficulty" name="difficulty" required>
                                                <option value="easy">Easy</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="hard">Hard</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="points_per_question" class="form-label">Points Per Question</label>
                                            <input type="number" class="form-control" id="points_per_question"
                                                   name="points_per_question" min="1" max="100" value="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="overall_points" class="form-label">Overall Points</label>
                                            <input type="number" class="form-control" id="overall_points"
                                                   name="overall_points" min="1" max="2000" value="5" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary-custom w-100" id="generateBtn">
                                            <i class="fas fa-magic me-2"></i>Generate Questions
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>AI Question Generator</h6>
                                <p class="mb-2">Generate multiple-choice questions using AI:</p>
                                <ul class="mb-0 small">
                                    <li>Enter topic and number of questions</li>
                                    <li>Questions include 4 options (A-D)</li>
                                    <li>Correct answer automatically marked</li>
                                    <li>Questions added to selected exam</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>Generating Questions...</h5>
                        <p class="text-muted">AI is creating your questions. This may take a few seconds.</p>
                    </div>

                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Generated Questions</h6>
                            <span class="badge bg-success">Added to exam</span>
                        </div>
                        <div id="generatedQuestions" class="question-list">
                            <!-- Questions will be inserted here -->
                        </div>
                    </div>

                    <!-- Existing Questions -->
                    <div id="existingQuestionsSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Exam Questions</h6>
                            <span class="badge bg-primary" id="questionCount">0 questions</span>
                        </div>
                        <div id="existingQuestions" class="question-list">
                            <!-- Existing questions will be shown here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.question-list {
    max-height: 400px;
    overflow-y: auto;
}

.question-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.question-item.ai-generated {
    border-left: 4px solid #28a745;
}

.option-item {
    padding: 5px 10px;
    margin: 2px 0;
    border-radius: 4px;
}

.option-item.correct {
    background-color: #d4edda;
    font-weight: bold;
}
</style>

<script>
let currentExamId = null;
let generatedQuestions = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

document.addEventListener('DOMContentLoaded', function() {
    syncOverallPoints();
});

// Handle exam selection
document.getElementById('exam_id').addEventListener('change', function() {
    currentExamId = this.value;
    if (currentExamId) {
        loadExistingQuestions(currentExamId);
    } else {
        document.getElementById('existingQuestionsSection').style.display = 'none';
    }
});

// Handle form submission
document.getElementById('aiQuestionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    generateQuestions(this);
});

document.getElementById('number_of_questions').addEventListener('input', syncOverallPoints);
document.getElementById('points_per_question').addEventListener('input', syncOverallPoints);

function syncOverallPoints() {
    const questionCount = document.getElementById('number_of_questions');
    const pointsEach = document.getElementById('points_per_question');
    const overallPoints = document.getElementById('overall_points');

    overallPoints.value = Math.max(1, Number(questionCount.value || 0) * Number(pointsEach.value || 0));
}

function generateQuestions(form) {
    const formData = new FormData(form);
    
    // Show loading state
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('resultsSection').style.display = 'none';
    
    // Disable button
    const generateBtn = document.getElementById('generateBtn');
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    
    fetch(@json(route('teacher.ai-questions.generate')), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: formData
    })
        .then(async response => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error generating questions');
            }
            return data;
        })
        .then(data => {
            generatedQuestions = data.questions || [];
            displayGeneratedQuestions(generatedQuestions);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('resultsSection').style.display = 'block';
            loadExistingQuestions(formData.get('exam_id'));
            showInlineMessage(data.message, 'success');
        })
        .catch(error => {
            document.getElementById('loadingState').style.display = 'none';
            showInlineMessage(error.message, 'danger');
        })
        .finally(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Generate Questions';
        });
}

function displayGeneratedQuestions(questions) {
    const container = document.getElementById('generatedQuestions');
    container.innerHTML = '';
    
    questions.forEach((question, index) => {
        const questionHtml = `
            <div class="question-item ai-generated">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">Question ${index + 1}</h6>
                    <span class="badge bg-success">AI Generated</span>
                </div>
                <p class="mb-2">${question.question_text}</p>
                <div class="options">
                    <div class="option-item ${question.correct_answer === 'A' ? 'correct' : ''}">
                        <strong>A.</strong> ${question.option_a}
                    </div>
                    <div class="option-item ${question.correct_answer === 'B' ? 'correct' : ''}">
                        <strong>B.</strong> ${question.option_b}
                    </div>
                    <div class="option-item ${question.correct_answer === 'C' ? 'correct' : ''}">
                        <strong>C.</strong> ${question.option_c}
                    </div>
                    <div class="option-item ${question.correct_answer === 'D' ? 'correct' : ''}">
                        <strong>D.</strong> ${question.option_d}
                    </div>
                </div>
                <div class="mt-2"><span class="badge bg-secondary">${question.points} point${Number(question.points) === 1 ? '' : 's'}</span></div>
            </div>
        `;
        container.innerHTML += questionHtml;
    });
}

function loadExistingQuestions(examId) {
    fetch(`/teacher/ai-questions/exam/${examId}/questions`, {
        headers: { 'Accept': 'application/json' },
    })
        .then(response => response.json())
        .then(data => displayExistingQuestions(data.questions || []))
        .catch(() => displayExistingQuestions([]));
}

function displayExistingQuestions(questions) {
    const container = document.getElementById('existingQuestions');
    const section = document.getElementById('existingQuestionsSection');
    const count = document.getElementById('questionCount');
    
    if (questions.length > 0) {
        section.style.display = 'block';
        count.textContent = `${questions.length} question${questions.length > 1 ? 's' : ''}`;
        
        container.innerHTML = '';
        questions.forEach((question, index) => {
            const questionHtml = `
                <div class="question-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">Question ${index + 1}</h6>
                        <span class="badge ${question.is_ai_generated ? 'bg-success' : 'bg-primary'}">${question.is_ai_generated ? 'AI Generated' : 'Manual'}</span>
                    </div>
                    <p class="mb-2">${question.question_text}</p>
                    <div class="options">
                        <div class="option-item ${question.correct_answer === 'A' ? 'correct' : ''}">
                            <strong>A.</strong> ${question.option_a}
                        </div>
                        <div class="option-item ${question.correct_answer === 'B' ? 'correct' : ''}">
                            <strong>B.</strong> ${question.option_b}
                        </div>
                        <div class="option-item ${question.correct_answer === 'C' ? 'correct' : ''}">
                            <strong>C.</strong> ${question.option_c}
                        </div>
                        <div class="option-item ${question.correct_answer === 'D' ? 'correct' : ''}">
                            <strong>D.</strong> ${question.option_d}
                        </div>
                    </div>
                <div class="mt-2"><span class="badge bg-secondary">${question.points} point${Number(question.points) === 1 ? '' : 's'}</span></div>
                </div>
            `;
            container.innerHTML += questionHtml;
        });
    } else {
        section.style.display = 'none';
    }
}

function showInlineMessage(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.querySelector('.card-body').prepend(alert);
    setTimeout(() => alert.remove(), 4000);
}
</script>
@endsection
