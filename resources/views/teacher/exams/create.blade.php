@extends('layouts.admin')

@section('title', 'Create Exam')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create New Exam</h5>
        <small class="text-muted">Fill in the exam details below</small>
    </div>
    <div class="card-body">
        @if($classes->isEmpty() || $subjects->isEmpty())
            <div class="alert alert-warning">
                You do not have any assigned class subjects yet. Please contact the admin or HOD before creating an exam.
            </div>
        @endif
        <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.store') }}">
            @csrf
            
            <div class="row g-3 g-md-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Exam Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="{{ old('title') }}"
                               placeholder="e.g., Mathematics Midterm Exam" required>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               min="1" max="300" value="{{ old('duration_minutes', 60) }}" required>
                    </div>
                </div>
            </div>

            <div class="row g-3 g-md-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select class="form-select class-subject-select" id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" data-class-id="{{ $subject->school_class_id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="school_class_id" class="form-label">Class</label>
                        <select class="form-select class-filter-select" id="school_class_id" name="school_class_id" data-subject-select="subject_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row g-3 g-md-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time (Optional)</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="{{ old('start_time') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time (Optional)</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="{{ old('end_time') }}">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" @checked(old('shuffle_questions'))>
                        <label class="form-check-label" for="shuffle_questions">
                            Shuffle Questions
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="show_results" name="show_results" value="1" @checked(old('show_results', true))>
                        <label class="form-check-label" for="show_results">
                            Show Results to Students
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_live" name="is_live" value="1" @checked(old('is_live'))>
                        <label class="form-check-label" for="is_live">
                            Make Exam Live Immediately
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route(($routePrefix ?? 'teacher') . '.exams') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Exams
                </a>
                <button type="submit" class="btn btn-primary-custom" @disabled($classes->isEmpty() || $subjects->isEmpty())>
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

document.querySelectorAll('.class-filter-select').forEach((select) => {
    select.addEventListener('change', () => filterExamSubjects(select));
    filterExamSubjects(select);
});
</script>
@endsection
