@extends('layouts.admin')

@section('title', 'My Exams')

@section('content')
<div class="teacher-exams-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">My Exams</h4>
            <p class="text-muted mb-0">A list of all exams you have created.</p>
        </div>
        <a href="{{ route(($routePrefix ?? 'teacher') . '.exams.create') }}" class="btn btn-primary-custom">
            <i class="fas fa-plus me-2"></i>Create Exam
        </a>
    </div>

    <div class="exam-table-shell">
        <div class="table-responsive">
            <table class="table align-middle exam-table mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Questions</th>
                        <th>Attempts</th>
                        <th>Avg Score</th>
                        <th>Published</th>
                        <th>Results Visible</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $exam->subject->name ?? $exam->title }}</div>
                                <small class="text-muted">{{ $exam->title }}</small>
                            </td>
                            <td>{{ $exam->schoolClass->full_name ?? 'N/A' }}</td>
                            <td>{{ $exam->questions_count }}</td>
                            <td>{{ $exam->attempts_count }}</td>
                            <td>
                                {{ is_null($exam->attempts_avg_percentage) ? 'N/A' : number_format($exam->attempts_avg_percentage, 1) . '%' }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.toggle-live', $exam) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="status-toggle {{ $exam->is_live ? 'is-on' : '' }}" title="Toggle publish status">
                                        <span class="status-knob"></span>
                                        <span class="status-label">{{ $exam->is_live ? 'Live' : 'Offline' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.toggle-results', $exam) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="status-toggle {{ $exam->show_results ? 'is-on' : '' }}" title="Toggle result visibility">
                                        <span class="status-knob"></span>
                                        <span class="status-label">{{ $exam->show_results ? 'On' : 'Off' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <div class="exam-actions">
                                    <a href="{{ route(($routePrefix ?? 'teacher') . '.exams.show', $exam) }}" class="icon-action" title="Open exam">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route(($routePrefix ?? 'teacher') . '.results.show', $exam) }}" class="icon-action" title="View attempts">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="{{ route(($routePrefix ?? 'teacher') . '.exams.edit', $exam) }}" class="icon-action" title="Edit exam">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.exams.destroy', $exam) }}" class="d-inline" onsubmit="return confirm('Delete this exam and all its questions?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action text-danger" title="Delete exam">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No exams have been created yet.
                                <div class="mt-3">
                                    <a href="{{ route(($routePrefix ?? 'teacher') . '.exams.create') }}" class="btn btn-primary-custom">
                                        <i class="fas fa-plus me-2"></i>Create First Exam
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $exams->links() }}
    </div>
</div>

<style>
.teacher-exams-page {
    color: #0a1931;
}

.exam-table-shell {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 8px;
    overflow: hidden;
}

.exam-table th {
    color: #5e6a78;
    font-weight: 600;
    border-bottom: 1px solid #e8edf3;
    white-space: nowrap;
}

.exam-table td {
    border-bottom: 1px solid #edf1f5;
    white-space: nowrap;
}

.status-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    background: transparent;
    padding: 0;
    color: #0a1931;
}

.status-knob {
    width: 38px;
    height: 22px;
    border-radius: 999px;
    background: #e8edf3;
    box-shadow: inset 0 0 0 1px rgba(10, 25, 49, .06);
    position: relative;
}

.status-knob::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(10, 25, 49, .25);
    transition: transform .2s ease;
}

.status-toggle.is-on .status-knob {
    background: #0a1931;
}

.status-toggle.is-on .status-knob::after {
    transform: translateX(16px);
}

.status-label {
    min-width: 52px;
    border-radius: 7px;
    padding: 4px 10px;
    background: #e6eef8;
    color: #0a1931;
    font-size: .78rem;
    font-weight: 700;
    text-align: center;
}

.status-toggle.is-on .status-label {
    background: #0a1931;
    color: #fff;
}

.exam-actions {
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.icon-action {
    border: 0;
    background: transparent;
    color: #0a1931;
    padding: 4px;
    line-height: 1;
    text-decoration: none;
}
</style>
@endsection
