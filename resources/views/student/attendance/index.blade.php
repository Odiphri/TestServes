@extends('layouts.admin')

@section('title', 'My Attendance')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="mb-0">Attendance Records</h5>
        <small class="text-muted">Track your attendance history</small>
    </div>
    <div class="card-body">
        <!-- Attendance Summary -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">{{ $stats['present'] }}</div>
                        <div class="stat-label">Days Present</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">{{ $stats['absent'] }}</div>
                        <div class="stat-label">Days Absent</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">{{ $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : 0 }}%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Records -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="d-none d-md-table-cell">Class</th>
                        <th class="text-center">Status</th>
                        <th class="text-center d-none d-lg-table-cell">Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $record)
                        <tr>
                            <td class="d-none d-md-table-cell">
                                <div class="d-md-none">{{ $record->attendance_date->format('M d, Y') }}</div>
                                <small class="d-md-none d-block text-muted">{{ $record->attendance_date->format('h:i A') }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $record->schoolClass->full_name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $record->status === 'present' ? 'bg-success' : 'bg-danger' }}">
                                    <i class="fas fa-{{ $record->status === 'present' ? 'check' : 'times' }} me-1"></i>
                                    <span class="d-none d-md-inline ms-1">{{ ucfirst($record->status) }}</span>
                                </span>
                            </td>
                            <td class="text-center d-none d-lg-table-cell">{{ $record->markedBy->full_name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                                <p>No attendance Records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stats['total'] > 0)
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-info-circle me-2"></i>Attendance Summary</h6>
            <p class="mb-0">
                You have attended {{ $stats['present'] }} out of {{ $stats['total'] }} school days 
                ({{ round(($stats['present'] / $stats['total']) * 100, 1) }}% attendance rate).
                @if($stats['absent'] > 0)
                You have been absent for {{ $stats['absent'] }} days.
                @endif
            </p>
        </div>
        @endif

        {{ $attendance->links() }}
    </div>
</div>
@endsection
