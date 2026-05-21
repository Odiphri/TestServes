@extends('layouts.admin')

@section('title', 'Create Exam')

@section('content')
@php
    $selectedTargetClassIds = collect(old('target_class_ids', []))->map(fn ($classId) => (int) $classId)->all();
@endphp
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create New Exam</h5>
        <small class="text-muted">Admin exam creation - can assign to any class and teacher</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.exams.store') }}">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Exam Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="e.g., Mathematics Midterm Exam" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               min="1" max="300" value="60" required>
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
                                <option value="{{ $subject->id }}" data-class-id="{{ $subject->school_class_id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? '' }}</option>
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
                                <option value="{{ $class->id }}" data-level="{{ $class->level }}">{{ $class->full_name }}</option>
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
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
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
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time (Optional)</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="shuffle_questions" value="0">
                        <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1">
                        <label class="form-check-label" for="shuffle_questions">
                            Shuffle Questions
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="show_results" value="0">
                        <input class="form-check-input" type="checkbox" id="show_results" name="show_results" value="1" checked>
                        <label class="form-check-label" for="show_results">
                            Show Results to Students
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="is_live" value="0">
                        <input class="form-check-input" type="checkbox" id="is_live" name="is_live" value="1">
                        <label class="form-check-label" for="is_live">
                            Make Exam Live Immediately
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="allow_review" value="0">
                        <input class="form-check-input" type="checkbox" id="allow_review" name="allow_review" value="1" checked>
                        <label class="form-check-label" for="allow_review">
                            Allow Review After Submission
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.exams') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Exams
                </a>
                <button type="submit" class="btn btn-primary-custom">
                    <i class="fas fa-save me-2"></i>Create Exam
                </button>
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
        document.getElementById('end_time').value = endDate.toISOString().slice(0, 16);
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
