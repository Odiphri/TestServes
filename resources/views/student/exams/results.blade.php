@extends('layouts.admin')

@section('title', $exam->title . ' Results')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Exam Results - {{ $exam->title }}</h5>
                    <small class="text-muted">{{ $exam->subject->name }}</small>
                </div>
                <div class="card-body">
                    <!-- Score Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $attempt->score }}/{{ $attempt->total_points }}</h3>
                                <p class="mb-0">Score</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success">{{ number_format($attempt->percentage, 1) }}%</h3>
                                <p class="mb-0">Percentage</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">{{ $attempt->grade }}</h3>
                                <p class="mb-0">Grade</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-muted">{{ $attempt->submitted_at->format('M d, Y H:i') }}</h3>
                                <p class="mb-0">Submitted</p>
                            </div>
                        </div>
                    </div>

                    <!-- Question Breakdown -->
                    <div class="question-results">
                        <h6 class="mb-3">Question Breakdown</h6>
                        @foreach($questions as $index => $question)
                        <div class="question-result mb-3 p-3 rounded {{ 
                            isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer 
                                ? 'bg-success bg-opacity-10 border border-success' 
                                : 'bg-danger bg-opacity-10 border border-danger' 
                        }}">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-{{ 
                                    isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer 
                                        ? 'success' 
                                        : 'danger' 
                                }} me-3">{{ $index + 1 }}</span>
                                <div class="flex-grow-1">
                                    <div class="mb-2 question-content"><strong>{!! $question->question_text !!}</strong></div>
                                    @if($question->image_path)
                                        <img class="question-image" src="{{ asset('storage/' . $question->image_path) }}" alt="Question image">
                                    @endif
                                    
                                    <div class="options-review">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="option-item {{ 
                                                    isset($answers[$question->id]) && $answers[$question->id] === 'A' 
                                                        ? 'selected-answer' 
                                                        : '' 
                                                }} {{ $question->correct_answer === 'A' ? 'correct-answer' : '' }}">
                                                    <strong>A.</strong> {{ $question->option_a }}
                                                    @if($question->correct_answer === 'A')
                                                        <i class="fas fa-check text-success ms-2"></i>
                                                    @endif
                                                    @if(isset($answers[$question->id]) && $answers[$question->id] === 'A' && $question->correct_answer !== 'A')
                                                        <i class="fas fa-times text-danger ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="option-item {{ 
                                                    isset($answers[$question->id]) && $answers[$question->id] === 'B' 
                                                        ? 'selected-answer' 
                                                        : '' 
                                                }} {{ $question->correct_answer === 'B' ? 'correct-answer' : '' }}">
                                                    <strong>B.</strong> {{ $question->option_b }}
                                                    @if($question->correct_answer === 'B')
                                                        <i class="fas fa-check text-success ms-2"></i>
                                                    @endif
                                                    @if(isset($answers[$question->id]) && $answers[$question->id] === 'B' && $question->correct_answer !== 'B')
                                                        <i class="fas fa-times text-danger ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="option-item {{ 
                                                    isset($answers[$question->id]) && $answers[$question->id] === 'C' 
                                                        ? 'selected-answer' 
                                                        : '' 
                                                }} {{ $question->correct_answer === 'C' ? 'correct-answer' : '' }}">
                                                    <strong>C.</strong> {{ $question->option_c }}
                                                    @if($question->correct_answer === 'C')
                                                        <i class="fas fa-check text-success ms-2"></i>
                                                    @endif
                                                    @if(isset($answers[$question->id]) && $answers[$question->id] === 'C' && $question->correct_answer !== 'C')
                                                        <i class="fas fa-times text-danger ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="option-item {{ 
                                                    isset($answers[$question->id]) && $answers[$question->id] === 'D' 
                                                        ? 'selected-answer' 
                                                        : '' 
                                                }} {{ $question->correct_answer === 'D' ? 'correct-answer' : '' }}">
                                                    <strong>D.</strong> {{ $question->option_d }}
                                                    @if($question->correct_answer === 'D')
                                                        <i class="fas fa-check text-success ms-2"></i>
                                                    @endif
                                                    @if(isset($answers[$question->id]) && $answers[$question->id] === 'D' && $question->correct_answer !== 'D')
                                                        <i class="fas fa-times text-danger ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <a href="{{ route('student.exams') }}" class="btn btn-primary-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to Exams
                        </a>
                        <button onclick="window.print()" class="btn btn-secondary ms-2">
                            <i class="fas fa-print me-2"></i>Print Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.question-result {
    border: 1px solid #dee2e6;
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

.option-item {
    padding: 8px 12px;
    margin-bottom: 4px;
    border-radius: 4px;
}

.correct-answer {
    background-color: #d4edda;
    font-weight: bold;
}

.selected-answer {
    background-color: #f8f9fa;
    border: 1px solid #007bff;
}

@media print {
    .btn {
        display: none;
    }
}
</style>
@endsection
