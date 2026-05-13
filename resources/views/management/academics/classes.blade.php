@extends('layouts.admin')

@section('title', 'Class Management')

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

<div class="row">
    <div class="col-lg-4">
        <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-class-panel" aria-expanded="false">
            <i class="fas fa-plus me-2"></i>Create Class
        </button>
        <div class="card collapse d-lg-block {{ $errors->any() ? 'show' : '' }}" id="create-class-panel">
            <div class="card-header">Create Class</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.classes.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. JSS1A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select" required>
                            <option value="">Select level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" @selected(old('level') === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stream</label>
                        <select name="stream" class="form-select">
                            <option value="">Select stream</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream }}" @selected(old('stream') === $stream)>{{ $stream }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create-class-active" checked>
                        <label class="form-check-label" for="create-class-active">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                        <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-class-panel">Cancel</button>
                        <button class="btn btn-primary-custom flex-grow-1">Create Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Classes</div>
            <div class="card-body table-responsive">
                <form method="GET" action="{{ route($routePrefix . '.classes') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-md-9">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Class name, level, stream">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.classes') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>

                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Stream</th>
                            <th>Assigned Staff</th>
                            <th>Subjects</th>
                            <th>Status</th>
                            <th>Save</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                            <tr>
                                <form method="POST" action="{{ route($routePrefix . '.classes.update', $class) }}">
                                    @csrf
                                    @method('PUT')
                                    <td><input class="form-control" name="name" value="{{ $class->name }}" required></td>
                                    <td>
                                        <select class="form-select" name="level" required>
                                            @foreach($levels as $level)
                                                <option value="{{ $level }}" @selected($class->level === $level)>{{ $level }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" name="stream">
                                            <option value="">None</option>
                                            @foreach($streams as $stream)
                                                <option value="{{ $stream }}" @selected($class->stream === $stream)>{{ $stream }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>{{ $class->assignedStaff->pluck('full_name')->join(', ') ?: 'None' }}</td>
                                    <td>{{ $class->subjects->pluck('name')->join(', ') ?: 'None' }}</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($class->is_active)>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </td>
                                    <td><button class="btn btn-sm btn-primary-custom">Save</button></td>
                                </form>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">No classes found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $classes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
