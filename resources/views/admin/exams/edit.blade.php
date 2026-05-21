@extends('layouts.admin')

@section('title', 'Edit Exam')

@section('content')
@php
    $selectedTargetClassIds = collect(old('target_class_ids', $exam->target_class_ids ?: [$exam->school_class_id]))->map(fn ($classId) => (int) $classId)->all();
@endphp
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Exam: {{ $exam->title }}</h5>
        <small class="text-muted">{{ $exam->subject->name ?? 'No Subject' }} • {{ $exam->schoolClass->full_name ?? 'No Class' }}</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.exams.update', $exam->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Exam Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="{{ $exam->title }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               value="{{ $exam->duration_minutes }}" min="1" max="300" required>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select class="form-select class-subject-select" id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" data-class-id="{{ $subject->school_class_id }}" {{ $exam->subject_id == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} - {{ $subject->schoolClass->full_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="school_class_id" class="form-label">Class</label>
                        <select class="form-select class-filter-select" id="school_class_id" name="school_class_id" data-subject-select="subject_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" data-level="{{ $class->level }}" {{ $exam->school_class_id == $class->id ? 'selected' : '' }}>
                                    {{ $class->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="created_by" class="form-label">Teacher</label>
                        <select class="form-select" id="created_by" name="created_by" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $exam->created_by == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Who can take this exam?</label>
                <div class="target-class-panel">
                    @foreach($classes as $class)
                        <label class="target-class-option" data-level="{{ $class->level }}">
                            <input type="checkbox" name="target_class_ids[]" value="{{ $class->id }}" @checked(in_array($class->id, $selectedTargetClassIds, true))>
                            <span>{{ $class->full_name }}</span>
                        </label>
                    @endforeach
                </div>
                <small class="text-muted">Choose one arm/stream or select every option shown for the whole level.</small>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time (Optional)</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" 
                               value="{{ $exam->start_time ? $exam->start_time->format('Y-m-d\TH:i') : '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time (Optional)</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" 
                               value="{{ $exam->end_time ? $exam->end_time->format('Y-m-d\TH:i') : '' }}">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="shuffle_questions" value="0">
                        <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" 
                               value="1" {{ $exam->shuffle_questions ? 'checked' : '' }}>
                        <label class="form-check-label" for="shuffle_questions">
                            Shuffle Questions
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="show_results" value="0">
                        <input class="form-check-input" type="checkbox" id="show_results" name="show_results" 
                               value="1" {{ $exam->show_results ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_results">
                            Show Results to Students
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="is_live" value="0">
                        <input class="form-check-input" type="checkbox" id="is_live" name="is_live" 
                               value="1" {{ $exam->is_live ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_live">
                            Make Exam Live
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="allow_review" value="0">
                        <input class="form-check-input" type="checkbox" id="allow_review" name="allow_review" 
                               value="1" {{ $exam->allow_review ?? true ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_review">
                            Allow Review After Submission
                        </label>
                    </div>
                </div>
            </div>

            <!-- Exam Questions Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Exam Questions ({{ $exam->questions->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($exam->questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Points</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exam->questions as $index => $question)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;">
                                                    {{ Str::limit($question->question_text, 80) }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($question->is_ai_generated)
                                                    <span class="badge bg-success">AI Generated</span>
                                                @else
                                                    <span class="badge bg-info">Manual</span>
                                                @endif
                                            </td>
                                            <td>{{ $question->points }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-outline-primary" title="Edit Question">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete Question" onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No questions added yet.</p>
                            <a href="#" class="btn btn-primary-custom">
                                <i class="fas fa-plus me-2"></i>Add Questions
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('admin.exams') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Exams
                    </a>
                    @if($exam->questions->count() > 0)
                        <a href="{{ route('admin.exams.show', $exam->id) }}" class="btn btn-info ms-2">
                            <i class="fas fa-eye me-2"></i>Preview Exam
                        </a>
                    @endif
                </div>
                <div>
                    <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" style="display: inline;" class="me-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this exam? This action cannot be undone.')">
                            <i class="fas fa-trash me-2"></i>Delete Exam
                        </button>
                    </form>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Update Exam
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = this.value;
    if (startTime) {
        const endDate = new Date(startTime);
        endDate.setHours(endDate.getHours() + 1);
        document.getElementById('end_time').min = startTime;
        if (!document.getElementById('end_time').value) {
            document.getElementById('end_time').value = endDate.toISOString().slice(0, 16);
        }
    }
});

document.getElementById('end_time').addEventListener('change', function() {
    const endTime = this.value;
    if (endTime) {
        document.getElementById('start_time').max = endTime;
    }
});

function filterExamSubjects(classSelect) {
    const subjectSelect = document.getElementById(classSelect.dataset.subjectSelect);
    if (!subjectSelect) return;

    Array.from(subjectSelect.options).forEach((option) => {
        if (!option.value) return;
        const visible = option.dataset.classId === classSelect.value;
        option.hidden = !visible;
        if (!visible && option.selected) {
            option.selected = false;
        }
    });
}

function filterTargetClasses(classSelect) {
    const selectedOption = classSelect.options[classSelect.selectedIndex];
    const selectedLevel = selectedOption ? selectedOption.dataset.level : '';

    document.querySelectorAll('.target-class-option').forEach((option) => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        const visible = option.dataset.level === selectedLevel;
        option.hidden = !visible;
        checkbox.disabled = !visible;

        if (!visible) {
            checkbox.checked = false;
        }

        if (visible && checkbox.value === classSelect.value) {
            checkbox.checked = true;
        }
    });
}

document.querySelectorAll('.class-filter-select').forEach((select) => {
    select.addEventListener('change', () => {
        filterExamSubjects(select);
        filterTargetClasses(select);
    });
    filterExamSubjects(select);
    filterTargetClasses(select);
});
</script>
<style>
.target-class-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.target-class-option {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #dbe4ee;
    border-radius: 8px;
    padding: 9px 12px;
    background: #fff;
}
</style>
@endsection
