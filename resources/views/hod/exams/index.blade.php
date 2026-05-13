@extends('layouts.admin')

@section('title', 'HOD Exams')

@section('content')
<div class="card">
    <div class="card-header">All Exams</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Created By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->title }}</td>
                            <td>{{ $exam->schoolClass->full_name ?? 'N/A' }}</td>
                            <td>{{ $exam->subject->name ?? 'N/A' }}</td>
                            <td>{{ $exam->creator->full_name ?? 'N/A' }}</td>
                            <td>{{ $exam->is_live ? 'Live' : 'Offline' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No exams found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exams->links() }}
    </div>
</div>
@endsection
