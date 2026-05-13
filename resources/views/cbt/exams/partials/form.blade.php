<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $exam->title) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Duration Minutes</label>
        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" min="1" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $exam->description) }}</textarea>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Class</label>
        <select name="school_class_id" class="form-control class-filter-select" data-subject-select="subject_id" required>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('school_class_id', $exam->school_class_id) == $class->id)>{{ $class->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Subject</label>
        <select name="subject_id" id="subject_id" class="form-control class-subject-select" required>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" data-class-id="{{ $subject->school_class_id }}" @selected(old('subject_id', $exam->subject_id) == $subject->id)>{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? '' }}</option>
            @endforeach
        </select>
    </div>
</div>

<script>
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
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Start Time</label>
        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time', optional($exam->start_time)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">End Time</label>
        <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time', optional($exam->end_time)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Pass Mark</label>
        <input type="number" name="pass_mark" class="form-control" value="{{ old('pass_mark', $exam->pass_mark ?? 50) }}" min="0" max="100" required>
    </div>
</div>
<div class="mb-3 d-flex gap-4">
    <label><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $exam->shuffle_questions))> Shuffle questions</label>
    <label><input type="checkbox" name="show_results" value="1" @checked(old('show_results', $exam->show_results ?? true))> Show results</label>
    <label><input type="checkbox" name="is_live" value="1" @checked(old('is_live', $exam->is_live))> Live</label>
</div>
