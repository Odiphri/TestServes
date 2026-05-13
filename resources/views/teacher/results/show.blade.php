@extends('layouts.admin')

@section('title', $exam->title . ' Results')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ $exam->title }} Results</span>
            <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.results.export', $exam) }}">
                @csrf
                <button class="btn btn-sm btn-light">Export CSV</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exam->attempts as $attempt)
                            <tr>
                                <td>{{ $attempt->student->full_name ?? 'N/A' }}</td>
                                <td>{{ $attempt->score }} / {{ $attempt->total_points }}</td>
                                <td>{{ $attempt->percentage }}%</td>
                                <td>{{ $attempt->grade }}</td>
                                <td>{{ optional($attempt->submitted_at)->format('M j, Y g:i A') ?? 'Not submitted' }}</td>
                                <td>
                                    <form method="POST" action="{{ route(($routePrefix ?? 'teacher') . '.results.retakes.allow', [$exam, $attempt]) }}" onsubmit="return confirm('Allow this student to retake the exam? This will remove their current attempt.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Allow Retake</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">No attempts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
