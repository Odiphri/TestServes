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
                        <select name="level" class="form-select class-level-select" data-stream-select="create-class-stream" required>
                            <option value="">Select level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" @selected(old('level') === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stream</label>
                        <select name="stream" id="create-class-stream" class="form-select class-stream-select">
                            <option value="">Select stream</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream }}" data-section="{{ in_array($stream, $jssStreams, true) ? 'jss' : 'sss' }}" @selected(old('stream') === $stream)>{{ $stream }}</option>
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
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix . '.classes') }}" class="row g-2 align-items-end mb-4">
                    <div class="col-12 col-md-9">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Class name, level, stream">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.classes') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>

                <div class="d-none d-lg-block table-responsive">
                    <table class="table table-striped table-hover align-middle class-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Stream</th>
                                <th>Assigned Staff</th>
                                <th>Subjects</th>
                                <th>Status</th>
                                <th class="text-end">Save</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $class)
                                <tr>
                                    <td colspan="7" class="p-0">
                                        <form method="POST" action="{{ route($routePrefix . '.classes.update', $class) }}" class="class-row-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="class-row-cell"><input class="form-control" name="name" value="{{ $class->name }}" required></div>
                                            <div class="class-row-cell">
                                                <select class="form-select class-level-select" name="level" data-stream-select="class-stream-{{ $class->id }}" required>
                                                    @foreach($levels as $level)
                                                        <option value="{{ $level }}" @selected($class->level === $level)>{{ $level }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="class-row-cell">
                                                <select class="form-select class-stream-select" name="stream" id="class-stream-{{ $class->id }}">
                                                    <option value="">None</option>
                                                    @foreach($streams as $stream)
                                                        <option value="{{ $stream }}" data-section="{{ in_array($stream, $jssStreams, true) ? 'jss' : 'sss' }}" @selected($class->stream === $stream)>{{ $stream }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="class-row-cell text-muted">{{ $class->assignedStaff->pluck('full_name')->join(', ') ?: 'None' }}</div>
                                            <div class="class-row-cell text-muted">{{ $class->subjects->pluck('name')->join(', ') ?: 'None' }}</div>
                                            <div class="class-row-cell">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($class->is_active)>
                                                    <label class="form-check-label">Active</label>
                                                </div>
                                            </div>
                                            <div class="class-row-cell text-end"><button class="btn btn-sm btn-primary-custom">Save</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted">No classes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-lg-none class-mobile-list">
                    @forelse($classes as $class)
                        <form method="POST" action="{{ route($routePrefix . '.classes.update', $class) }}" class="class-mobile-card">
                            @csrf
                            @method('PUT')

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <div class="class-mobile-title">{{ $class->name }}</div>
                                    <div class="class-mobile-subtitle">{{ $class->level }}{{ $class->stream ? ' - ' . $class->stream : '' }}</div>
                                </div>
                                <span class="badge {{ $class->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $class->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" name="name" value="{{ $class->name }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Level</label>
                                    <select class="form-select class-level-select" name="level" data-stream-select="mobile-class-stream-{{ $class->id }}" required>
                                        @foreach($levels as $level)
                                            <option value="{{ $level }}" @selected($class->level === $level)>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Stream</label>
                                    <select class="form-select class-stream-select" name="stream" id="mobile-class-stream-{{ $class->id }}">
                                        <option value="">None</option>
                                        @foreach($streams as $stream)
                                            <option value="{{ $stream }}" data-section="{{ in_array($stream, $jssStreams, true) ? 'jss' : 'sss' }}" @selected($class->stream === $stream)>{{ $stream }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="class-mobile-meta">
                                <div>
                                    <span>Assigned Staff</span>
                                    <strong>{{ $class->assignedStaff->pluck('full_name')->join(', ') ?: 'None' }}</strong>
                                </div>
                                <div>
                                    <span>Subjects</span>
                                    <strong>{{ $class->subjects->pluck('name')->join(', ') ?: 'None' }}</strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="class-active-{{ $class->id }}" @checked($class->is_active)>
                                    <label class="form-check-label" for="class-active-{{ $class->id }}">Active</label>
                                </div>
                                <button class="btn btn-primary-custom">Save</button>
                            </div>
                        </form>
                    @empty
                        <div class="text-muted py-4">No classes found.</div>
                    @endforelse
                </div>

                {{ $classes->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.class-table {
    table-layout: fixed;
}

.class-table th:nth-child(1) { width: 16%; }
.class-table th:nth-child(2) { width: 12%; }
.class-table th:nth-child(3) { width: 14%; }
.class-table th:nth-child(4) { width: 18%; }
.class-table th:nth-child(5) { width: 24%; }
.class-table th:nth-child(6) { width: 10%; }
.class-table th:nth-child(7) { width: 6%; }

.class-row-form {
    display: grid;
    grid-template-columns: 16% 12% 14% 18% 24% 10% 6%;
    align-items: center;
}

.class-row-cell {
    padding: .75rem;
    min-width: 0;
}

.class-row-cell:nth-child(4),
.class-row-cell:nth-child(5) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.class-mobile-list {
    display: grid;
    gap: 12px;
}

.class-mobile-card {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}

.class-mobile-title {
    color: #0a1931;
    font-weight: 700;
    font-size: 1rem;
}

.class-mobile-subtitle {
    color: #6c757d;
    font-size: .85rem;
    margin-top: 2px;
}

.class-mobile-meta {
    display: grid;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #edf1f5;
}

.class-mobile-meta span {
    display: block;
    color: #6c757d;
    font-size: .78rem;
    margin-bottom: 2px;
}

.class-mobile-meta strong {
    display: block;
    color: #0a1931;
    font-size: .9rem;
    font-weight: 600;
    overflow-wrap: anywhere;
}
</style>
<script>
function filterClassStreams(levelSelect) {
    const streamSelect = document.getElementById(levelSelect.dataset.streamSelect);
    if (!streamSelect) return;

    const section = levelSelect.value.startsWith('JSS') ? 'jss' : 'sss';
    Array.from(streamSelect.options).forEach((option) => {
        if (!option.value) return;
        const visible = option.dataset.section === section;
        option.hidden = !visible;
        if (!visible && option.selected) {
            option.selected = false;
        }
    });
}

document.querySelectorAll('.class-level-select').forEach((select) => {
    select.addEventListener('change', () => filterClassStreams(select));
    filterClassStreams(select);
});
</script>
@endsection
