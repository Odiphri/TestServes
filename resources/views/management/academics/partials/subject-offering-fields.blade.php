@php
    $section = str_starts_with($subject->schoolClass?->level ?? '', 'JSS') ? 'jss' : 'sss';
@endphp

<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
    <div>
        <div class="fw-semibold">{{ $subject->schoolClass->full_name ?? 'No class' }}</div>
        <div class="small text-muted">Class-specific offering</div>
    </div>
    <span class="badge {{ $subject->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $subject->is_active ? 'Active' : 'Inactive' }}</span>
</div>

<div class="row g-2">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="{{ $subject->name }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input class="form-control" name="code" value="{{ $subject->code }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Section</label>
        <select class="form-select" name="section" required>
            <option value="jss" @selected($section === 'jss')>JSS</option>
            <option value="sss" @selected($section === 'sss')>SSS</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Class</label>
        <select class="form-select" name="school_class_id" required>
            <optgroup label="JSS Classes">
                @foreach($jssClasses as $class)
                    <option value="{{ $class->id }}" @selected($subject->school_class_id === $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </optgroup>
            <optgroup label="SSS Classes">
                @foreach($sssClasses as $class)
                    <option value="{{ $class->id }}" @selected($subject->school_class_id === $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center gap-3 mt-3">
    <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="subject-active-{{ $subject->id }}" @checked($subject->is_active)>
        <label class="form-check-label" for="subject-active-{{ $subject->id }}">Active</label>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-danger" type="submit" form="delete-subject-{{ $subject->id }}" onclick="return confirm('Delete this class offering? Exams for this offering will also be deleted.')">Delete</button>
        <button class="btn btn-primary-custom">Save</button>
    </div>
</div>
