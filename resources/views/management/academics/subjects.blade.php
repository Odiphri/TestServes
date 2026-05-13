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
                        <label class="form-label">Class</label>
                        <select name="school_class_id" class="form-select" required>
                            <option value="">Select class</option>
                            <optgroup label="JSS Classes">
                                @foreach($jssClasses as $class)
                                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->full_name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="SSS Classes">
                                @foreach($sssClasses as $class)
                                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->full_name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
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
            <div class="card-body table-responsive">
                <form method="GET" action="{{ route($routePrefix . '.subjects') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Subject name or code">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>{{ $class->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.subjects') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>

                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Section</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Save</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            @php $section = str_starts_with($subject->schoolClass?->level ?? '', 'JSS') ? 'jss' : 'sss'; @endphp
                            <tr>
                                <form method="POST" action="{{ route($routePrefix . '.subjects.update', $subject) }}">
                                    @csrf
                                    @method('PUT')
                                    <td><input class="form-control" name="name" value="{{ $subject->name }}" required></td>
                                    <td><input class="form-control" name="code" value="{{ $subject->code }}" required></td>
                                    <td>
                                        <select class="form-select" name="section" required>
                                            <option value="jss" @selected($section === 'jss')>JSS</option>
                                            <option value="sss" @selected($section === 'sss')>SSS</option>
                                        </select>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($subject->is_active)>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </td>
                                    <td><button class="btn btn-sm btn-primary-custom">Save</button></td>
                                </form>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No subjects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $subjects->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
