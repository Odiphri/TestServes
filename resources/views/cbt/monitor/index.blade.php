@extends('layouts.admin')

@section('title', 'Exam Monitor')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Live Exams</div>
            <div class="card-body">
                @forelse($liveExams as $exam)
                    <p class="mb-2">
                        {{ $exam->title }}<br>
                        <small>
                            {{ $exam->subject->name ?? 'No subject' }} &middot; {{ $exam->schoolClass->full_name ?? 'No class' }}
                            <br>{{ $exam->active_attempts_count }} active, {{ $exam->submitted_attempts_count }} submitted
                        </small>
                    </p>
                @empty
                    <p class="text-muted">No live exams.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Active Attempts</div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Student</th><th>Class</th><th>Exam</th><th>Started</th><th>Last Seen</th><th>Expires</th></tr></thead>
                    <tbody>
                        @forelse($activeAttempts as $attempt)
                            <tr>
                                <td>{{ $attempt->student->full_name ?? 'N/A' }}</td>
                                <td>{{ $attempt->student->assignedClass->full_name ?? 'N/A' }}</td>
                                <td>{{ $attempt->exam->title ?? 'N/A' }}</td>
                                <td>{{ $attempt->started_at?->format('M d, H:i') ?? 'N/A' }}</td>
                                <td>{{ $attempt->updated_at?->format('M d, H:i') ?? 'N/A' }}</td>
                                <td>{{ $attempt->time_expired_at?->format('M d, H:i') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No active attempts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $activeAttempts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
