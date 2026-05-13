@extends('layouts.admin')

@section('title', $exam->title)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $exam->title }}</h5>
                        <small class="text-muted">{{ $exam->subject->name }} • {{ $exam->duration_minutes }} minutes</small>
                    </div>
                    <div class="exam-timer">
                        <i class="fas fa-clock me-2"></i>
                        <span id="timer" class="fw-bold">{{ $exam->duration_minutes }}:00</span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="examForm">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span id="questionProgress" class="text-muted small"></span>
                        </div>

                        <div class="question-container">
                            @foreach($questions as $index => $question)
                            <div class="question-card mb-4" data-question-id="{{ $question->id }}">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="question-number badge bg-primary me-3">{{ $index + 1 }}</span>
                                    <div class="mb-0 question-content">{!! $question->question_text !!}</div>
                                </div>

                                @if($question->image_path)
                                    <img class="question-image" src="{{ asset('storage/' . $question->image_path) }}" alt="Question image">
                                @endif
                                
                                <div class="options-container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[{{ $question->id }}]" 
                                                       value="A" 
                                                       id="q{{ $question->id }}_a">
                                                <label class="form-check-label" for="q{{ $question->id }}_a">
                                                    <strong>A.</strong> {{ $question->option_a }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[{{ $question->id }}]" 
                                                       value="B" 
                                                       id="q{{ $question->id }}_b">
                                                <label class="form-check-label" for="q{{ $question->id }}_b">
                                                    <strong>B.</strong> {{ $question->option_b }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[{{ $question->id }}]" 
                                                       value="C" 
                                                       id="q{{ $question->id }}_c">
                                                <label class="form-check-label" for="q{{ $question->id }}_c">
                                                    <strong>C.</strong> {{ $question->option_c }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[{{ $question->id }}]" 
                                                       value="D" 
                                                       id="q{{ $question->id }}_d">
                                                <label class="form-check-label" for="q{{ $question->id }}_d">
                                                    <strong>D.</strong> {{ $question->option_d }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="question-navigator">
                                @foreach($questions as $index => $question)
                                <button type="button" 
                                        class="nav-dot btn btn-sm me-2 {{ $index == 0 ? 'btn-primary' : 'btn-outline-secondary' }}" 
                                        data-question="{{ $index }}">
                                    {{ $index + 1 }}
                                </button>
                                @endforeach
                            </div>
                            
                            <div class="exam-actions">
                                <button type="button" id="saveProgressBtn" class="btn btn-secondary me-2">
                                    <i class="fas fa-save me-2"></i>Save Progress
                                </button>
                                <button type="button" id="submitExamBtn" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Exam
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Exam?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit your exam? Once submitted, you cannot make any changes.</p>
                <div id="unansweredQuestions" class="alert alert-warning"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSubmitBtn" class="btn btn-success">Submit Exam</button>
            </div>
        </div>
    </div>
</div>

<style>
.question-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
    display: none;
}

.question-number {
    font-size: 1.2rem;
    padding: 8px 12px;
}

.nav-dot {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-weight: bold;
}

.exam-timer {
    font-size: 1.2rem;
    color: #0a1931;
}

.question-container {
    min-height: 320px;
}

.form-check-input:checked {
    background-color: #0a1931;
    border-color: #0a1931;
}

.question-card.active {
    border-color: #0a1931;
    background: #e9ecef;
    display: block;
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
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    margin: 8px 0 16px;
}
</style>

<script>
let examDuration = {{ $exam->duration_minutes }} * 60; // Convert to seconds
let timer;
let currentQuestion = 0;
let answers = {};
let examStarted = false;
let isSubmitting = false;
const questionCards = document.querySelectorAll('.question-card');
const navDots = document.querySelectorAll('.nav-dot');
const totalQuestions = {{ $questions->count() }};

// Timer functionality
function startTimer() {
    if (examStarted) return;
    examStarted = true;
    
    timer = setInterval(function() {
        examDuration--;
        
        let minutes = Math.floor(examDuration / 60);
        let seconds = examDuration % 60;
        document.getElementById('timer').textContent = 
            minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        
        if (examDuration <= 0) {
            clearInterval(timer);
            autoSubmit();
        }
    }, 1000);
}

// Question navigation
navDots.forEach((dot, index) => {
    dot.addEventListener('click', function() {
        showQuestion(index);
    });
});

function showQuestion(index) {
    if (!questionCards[index]) return;

    questionCards.forEach(card => {
        card.classList.remove('active');
    });

    questionCards[index].classList.add('active');
    currentQuestion = index;

    navDots.forEach((dot, dotIndex) => {
        updateNavDotState(dotIndex);
    });

    document.getElementById('questionProgress').textContent =
        `Question ${index + 1} of ${totalQuestions}`;
}

function updateNavDotState(index) {
    const dot = navDots[index];
    const card = questionCards[index];
    if (!dot || !card) return;

    const isAnswered = !!card.querySelector('input[type="radio"]:checked');
    dot.classList.remove('btn-primary', 'btn-outline-secondary', 'btn-info', 'btn-success');

    if (index === currentQuestion) {
        dot.classList.add('btn-primary');
    } else if (isAnswered) {
        dot.classList.add('btn-success');
    } else {
        dot.classList.add('btn-outline-secondary');
    }
}

function goToNextQuestion() {
    const nextQuestion = currentQuestion + 1;

    if (nextQuestion < totalQuestions) {
        setTimeout(() => showQuestion(nextQuestion), 250);
        return;
    }

    setTimeout(() => {
        showNotification('Last question answered. You can submit your exam now.', 'success');
    }, 250);
}

// Auto-save functionality
document.getElementById('saveProgressBtn').addEventListener('click', function() {
    saveProgress();
});

function saveProgress() {
    const formData = new FormData(document.getElementById('examForm'));
    const answersObject = {};
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('answers[')) {
            const questionId = key.match(/answers\[(\d+)\]/)[1];
            answersObject[questionId] = value;
        }
    }
    
    fetch('/student/exams/{{ $exam->id }}/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            answers: answersObject
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Progress saved successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error saving progress:', error);
    });
}

// Submit exam
document.getElementById('submitExamBtn').addEventListener('click', function() {
    const unansweredCount = getUnansweredQuestions();
    
    if (unansweredCount > 0) {
        document.getElementById('unansweredQuestions').textContent = 
            `You have ${unansweredCount} unanswered question(s). Are you sure you want to submit?`;
    } else {
        document.getElementById('unansweredQuestions').textContent = 
            'All questions answered. Ready to submit!';
    }
    
    new bootstrap.Modal(document.getElementById('submitModal')).show();
});

document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
    submitExam();
});

function collectAnswers() {
    const formData = new FormData(document.getElementById('examForm'));
    const answersObject = {};

    for (let [key, value] of formData.entries()) {
        if (key.startsWith('answers[')) {
            const questionId = key.match(/answers\[(\d+)\]/)[1];
            answersObject[questionId] = value;
        }
    }

    return answersObject;
}

function submitExam(options = {}) {
    if (isSubmitting) return;
    isSubmitting = true;
    clearInterval(timer);

    const answersObject = collectAnswers();
    const requestOptions = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            answers: answersObject
        })
    };

    if (options.keepalive) {
        requestOptions.keepalive = true;
    }

    fetch('/student/exams/{{ $exam->id }}/submit', {
        ...requestOptions
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/student/exams/{{ $exam->id }}/results';
        } else {
            isSubmitting = false;
            showNotification('Error submitting exam: ' + data.error, 'error');
        }
    })
    .catch(error => {
        isSubmitting = false;
        console.error('Error submitting exam:', error);
        showNotification('Error submitting exam', 'error');
    });
}

function autoSubmit() {
    showNotification('Time expired! Auto-submitting your exam...', 'warning');
    submitExam();
}

function endExamForLeaving(reason) {
    if (!examStarted || isSubmitting) return;
    showNotification(reason, 'danger');
    submitExam({ keepalive: true });
}

function getUnansweredQuestions() {
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    return totalQuestions - answeredQuestions;
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Start timer when page loads
document.addEventListener('DOMContentLoaded', function() {
    startTimer();
    showQuestion(0);
});

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        endExamForLeaving('Exam ended because the page was minimized or left.');
    }
});

window.addEventListener('pagehide', function() {
    endExamForLeaving('Exam ended because the page was left.');
});

// Handle radio button clicks to update navigator
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const questionCard = this.closest('.question-card');
        const questionIndex = Array.from(questionCards).indexOf(questionCard);
        
        updateNavDotState(questionIndex);
        goToNextQuestion();
    });
});
</script>
@endsection
