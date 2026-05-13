@extends('layouts.admin')

@section('title', 'CBT Exams')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="mb-0">Exam Operations</h5>
        <a href="{{ route($routePrefix . '.exams.create') }}" class="btn btn-primary-custom btn-sm w-100 w-md-auto">
            <i class="fas fa-plus me-2"></i>Create Exam
        </a>
    </div>
    <div class="card-body p-0">
        @if($exams->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="d-none d-md-table-cell">Title</th>
                            <th class="d-none d-md-table-cell">Class</th>
                            <th class="d-none d-md-table-cell">Subject</th>
                            <th class="text-center">Questions</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            <tr>
                                <td class="d-none d-md-table-cell">
                                    <div class="fw-bold text-truncate">{{ $exam->title }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $exam->schoolClass->full_name ?? 'N/A' }}</td>
                                <td class="d-none d-md-table-cell">{{ $exam->subject->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $exam->questions()->count() }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $exam->is_live ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $exam->is_live ? 'Live' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route($routePrefix . '.exams.show', $exam) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                            <span class="d-none d-md-inline ms-1">View</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $exams->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Exams Available</h5>
                <p class="text-muted mb-4">No exams have been created in the system yet.</p>
                <a href="{{ route($routePrefix . '.exams.create') }}" class="btn btn-primary-custom">
                    <i class="fas fa-plus me-2"></i>Create First Exam
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
