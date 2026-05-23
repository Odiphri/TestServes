@extends('layouts.admin')

@section('title', 'Academic Sessions')

@section('content')
<div class="row">
    @if($canManageSessions)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Create Academic Session</div>
            <div class="card-body">
                <form method="POST" action="{{ route('academic-sessions.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" value="{{ old('academic_year') }}" placeholder="2024/2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <select name="term" class="form-select" required>
                            <option value="">Select term</option>
                            @foreach($terms as $term)
                                <option value="{{ $term }}" @selected(old('term') === $term)>{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Starts</label>
                            <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ends</label>
                            <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                        </div>
                    </div>
                    <button class="btn btn-primary-custom w-100 mt-4">Create Session</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="{{ $canManageSessions ? 'col-lg-8' : 'col-12' }}">
        <div class="card">
            <div class="card-header">Current Session</div>
            <div class="card-body">
                @if($activeSession)
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                        <div>
                            <h2 class="h5 mb-1">{{ $activeSession->display_name }}</h2>
                            <div class="text-muted">
                                Activated {{ $activeSession->activated_at?->format('M j, Y g:ia') ?? 'recently' }}
                            </div>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="session-stats mt-3">
                        <div>
                            <span>Auto-promoted</span>
                            <strong>{{ number_format($activeSession->promoted_students_count) }} students</strong>
                        </div>
                        <div>
                            <span>Date range</span>
                            <strong>
                                {{ $activeSession->starts_at?->format('M j, Y') ?? 'Not set' }}
                                -
                                {{ $activeSession->ends_at?->format('M j, Y') ?? 'Not set' }}
                            </strong>
                        </div>
                    </div>
                @else
                    <div class="text-muted">No active academic session has been set.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">All Sessions</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th>Promoted</th>
                                @if($canManageSessions)
                                    <th class="text-end">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr>
                                    <td>
                                        @if($canManageSessions)
                                            <form method="POST" action="{{ route('academic-sessions.update', $session) }}" class="session-edit-form">
                                                @csrf
                                                @method('PUT')
                                                <input class="form-control form-control-sm" name="academic_year" value="{{ old("academic_year_{$session->id}", $session->academic_year) }}" required>
                                                <select class="form-select form-select-sm" name="term" required>
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term }}" @selected($session->term === $term)>{{ $term }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="date" class="form-control form-control-sm" name="starts_at" value="{{ $session->starts_at?->format('Y-m-d') }}">
                                                <input type="date" class="form-control form-control-sm" name="ends_at" value="{{ $session->ends_at?->format('Y-m-d') }}">
                                                <button class="btn btn-sm btn-outline-secondary">Save</button>
                                            </form>
                                        @else
                                            <strong>{{ $session->display_name }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $session->starts_at?->format('M j, Y') ?? 'Not set' }}
                                        -
                                        {{ $session->ends_at?->format('M j, Y') ?? 'Not set' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $session->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $session->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($session->promoted_students_count) }}</td>
                                    @if($canManageSessions)
                                        <td class="text-end">
                                            @if(!$session->is_active)
                                                <form method="POST" action="{{ route('academic-sessions.activate', $session) }}" onsubmit="return confirm('Activate {{ $session->display_name }}? All students will be promoted to the next available class level.')" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-primary-custom">Activate</button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Current</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageSessions ? 5 : 4 }}" class="text-muted">No academic sessions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.session-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.session-stats div {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 12px;
}

.session-stats span {
    display: block;
    color: #6c757d;
    font-size: .78rem;
    margin-bottom: 3px;
}

.session-edit-form {
    display: grid;
    grid-template-columns: minmax(110px, 1fr) minmax(120px, 1fr) minmax(120px, 1fr) minmax(120px, 1fr) auto;
    gap: 8px;
}

@media (max-width: 768px) {
    .session-stats,
    .session-edit-form {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
