@extends('layouts.admin')

@section('title', 'Exams Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Exams Management</h5>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary-custom">
            <i class="fas fa-plus me-2"></i>Create Exam
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exam->title }}</td>
                        <td>{{ $exam->schoolClass->full_name ?? 'N/A' }}</td>
                        <td>{{ $exam->subject->name ?? 'N/A' }}</td>
                        <td>{{ $exam->creator->full_name ?? 'N/A' }}</td>
                        <td>{{ $exam->duration_minutes }} mins</td>
                        <td>
                            <span class="badge {{ $exam->is_live ? 'bg-success' : 'bg-secondary' }}">
                                {{ $exam->is_live ? 'Live' : 'Offline' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No exams created yet.
                            <br>
                            <a href="{{ route('admin.exams.create') }}" class="btn btn-primary-custom mt-2">
                                <i class="fas fa-plus me-2"></i>Create First Exam
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $exams->links() }}
    </div>
</div>
@endsection
