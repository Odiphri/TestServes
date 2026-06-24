@extends('layouts.admin')

@section('title', 'Subject Management')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the highlighted fields.</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $jssClasses = $classes->filter(fn ($class) => str_starts_with($class->level, 'JSS'));
    $sssClasses = $classes->filter(fn ($class) => str_starts_with($class->level, 'SS'));
    $selectedCreateClassIds = old('school_class_ids', old('school_class_id') ? [old('school_class_id')] : []);
@endphp

<div class="row">
    <div class="col-lg-4">
        <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-subject-panel" aria-expanded="false">
            <i class="fas fa-plus me-2"></i>Create Subject
        </button>
        <div class="card collapse d-lg-block {{ $errors->any() ? 'show' : '' }}" id="create-subject-panel">
            <div class="card-header">Create Subject</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.subjects.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Mathematics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g. MATH101" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <select name="section" class="form-select" required>
                            <option value="">Select section</option>
                            <option value="jss" @selected(old('section') === 'jss')>JSS</option>
                            <option value="sss" @selected(old('section') === 'sss')>SSS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Classes Offering This Subject</label>
                        <div class="subject-class-picker border rounded p-2">
                            <div class="small fw-semibold text-muted mb-2">JSS Classes</div>
                            <div class="subject-class-group mb-3">
                                @foreach($jssClasses as $class)
                                    <div class="form-check subject-class-option" data-section="jss">
                                        <input class="form-check-input" type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" id="create-subject-class-{{ $class->id }}" @checked(in_array($class->id, $selectedCreateClassIds))>
                                        <label class="form-check-label" for="create-subject-class-{{ $class->id }}">{{ $class->full_name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="small fw-semibold text-muted mb-2">SSS Classes</div>
                            <div class="subject-class-group">
                                @foreach($sssClasses as $class)
                                    <div class="form-check subject-class-option" data-section="sss">
                                        <input class="form-check-input" type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" id="create-subject-class-{{ $class->id }}" @checked(in_array($class->id, $selectedCreateClassIds))>
                                        <label class="form-check-label" for="create-subject-class-{{ $class->id }}">{{ $class->full_name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-text">Select every class that can offer this subject.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create-subject-active" checked>
                        <label class="form-check-label" for="create-subject-active">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                        <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-subject-panel">Cancel</button>
                        <button class="btn btn-primary-custom flex-grow-1">Create Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Subjects</div>
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix . '.subjects') }}" class="row g-2 align-items-end mb-4" data-auto-submit="true" data-live-search-target="subjects-results">
                    <div class="col-12 col-md-5">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Subject name or code">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>{{ $class->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.subjects') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>

                <div id="subjects-results" aria-live="polite">
                <div class="d-lg-none subject-mobile-list">
                    @forelse($subjectGroups as $group)
                        <div class="subject-mobile-card">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <div class="subject-mobile-title">{{ $group->name }}</div>
                                    <div class="subject-mobile-subtitle">{{ $group->code }} &middot; {{ strtoupper($group->section) }}</div>
                                </div>
                                <span class="badge {{ $group->all_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $group->active_count }}/{{ $group->class_count }} active
                                </span>
                            </div>

                            <div class="subject-class-summary mb-3">
                                <div class="fw-semibold">{{ $group->class_count }} {{ $group->class_count === 1 ? 'class' : 'classes' }} offering this subject</div>
                                <div class="text-muted small">{{ $group->class_names ?: 'No classes attached.' }}</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#mobile-subject-group-{{ $loop->index }}">
                                    Manage class offerings
                                </button>
                                <form method="POST" action="{{ route($routePrefix . '.subjects.group.destroy') }}" onsubmit="return confirm('Delete {{ $group->name }} from {{ strtoupper($group->section) }} completely? This removes all class offerings, assigned teachers/students for those offerings, and linked exams.')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="name" value="{{ $group->name }}">
                                    <input type="hidden" name="code" value="{{ $group->code }}">
                                    <input type="hidden" name="section" value="{{ $group->section }}">
                                    <button class="btn btn-outline-danger w-100" type="submit">Delete whole subject</button>
                                </form>
                            </div>

                            <div class="collapse mt-3" id="mobile-subject-group-{{ $loop->index }}">
                                @foreach($group->subjects as $subject)
                                    <form method="POST" action="{{ route($routePrefix . '.subjects.update', $subject) }}" class="subject-offering-editor">
                                        @csrf
                                        @method('PUT')
                                        @include('management.academics.partials.subject-offering-fields', ['subject' => $subject, 'jssClasses' => $jssClasses, 'sssClasses' => $sssClasses])
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-muted py-4">No subjects found.</div>
                    @endforelse
                </div>

                <div class="d-none d-lg-block table-responsive">
                <table class="table table-striped table-hover align-middle subject-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Section</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectGroups as $group)
                            <tr>
                                <td class="fw-semibold">{{ $group->name }}</td>
                                <td>{{ $group->code }}</td>
                                <td>{{ strtoupper($group->section) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $group->class_count }} {{ $group->class_count === 1 ? 'class' : 'classes' }}</span>
                                    <div class="small text-muted subject-class-names">{{ $group->class_names }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $group->all_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $group->active_count }}/{{ $group->class_count }} active
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#subject-group-{{ $loop->index }}">
                                            Manage
                                        </button>
                                        <form method="POST" action="{{ route($routePrefix . '.subjects.group.destroy') }}" onsubmit="return confirm('Delete {{ $group->name }} from {{ strtoupper($group->section) }} completely? This removes all class offerings, assigned teachers/students for those offerings, and linked exams.')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="name" value="{{ $group->name }}">
                                            <input type="hidden" name="code" value="{{ $group->code }}">
                                            <input type="hidden" name="section" value="{{ $group->section }}">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete whole subject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="subject-group-{{ $loop->index }}">
                                <td colspan="6">
                                    <div class="subject-offering-grid">
                                        @foreach($group->subjects as $subject)
                                            <form method="POST" action="{{ route($routePrefix . '.subjects.update', $subject) }}" class="subject-offering-editor">
                                                @csrf
                                                @method('PUT')
                                                @include('management.academics.partials.subject-offering-fields', ['subject' => $subject, 'jssClasses' => $jssClasses, 'sssClasses' => $sssClasses])
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No subjects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                @foreach($subjectGroups as $group)
                    @foreach($group->subjects as $subject)
                        <form id="delete-subject-{{ $subject->id }}" method="POST" action="{{ route($routePrefix . '.subjects.destroy', $subject) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endforeach

                {{ $subjectGroups->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.subject-table th,
.subject-table td {
    white-space: nowrap;
}

.subject-mobile-list {
    display: grid;
    gap: 12px;
}

.subject-mobile-card {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}

.subject-mobile-title {
    color: #0a1931;
    font-weight: 700;
    font-size: 1rem;
}

.subject-mobile-subtitle {
    color: #6c757d;
    font-size: .85rem;
    margin-top: 2px;
}

.subject-class-picker {
    max-height: 240px;
    overflow-y: auto;
}

.subject-class-group {
    display: grid;
    gap: 6px;
}

.subject-class-summary {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 10px 12px;
    background: #fbfcfe;
}

.subject-class-names {
    max-width: 320px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.subject-offering-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.subject-offering-editor {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sectionSelect = document.querySelector('select[name="section"]');
    const classOptions = document.querySelectorAll('.subject-class-option');

    function filterCreateClasses() {
        const selectedSection = sectionSelect ? sectionSelect.value : '';

        classOptions.forEach((option) => {
            const shouldShow = selectedSection === '' || option.dataset.section === selectedSection;
            option.classList.toggle('d-none', !shouldShow);

            if (!shouldShow) {
                const checkbox = option.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        });
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', filterCreateClasses);
        filterCreateClasses();
    }
});
</script>
@endsection
