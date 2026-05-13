@extends('layouts.admin')

@section('title', 'Attendance')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Mark Attendance - {{ $assignedClass->full_name }}</h5>
        <small class="text-muted">Your assigned class</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('teacher.attendance.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="attendance_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Student</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student->full_name }}</td>
                                <td>
                                    <select name="statuses[{{ $student->id }}]" class="form-control">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">No students in this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary">Save Attendance</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Attendance</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($attendance as $record)
                    <tr>
                        <td>{{ $record->attendance_date->format('M d, Y') }}</td>
                        <td>{{ $record->student->full_name ?? 'N/A' }}</td>
                        <td>{{ $record->schoolClass->full_name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($record->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No attendance records.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $attendance->links() }}
    </div>
</div>
@endsection
