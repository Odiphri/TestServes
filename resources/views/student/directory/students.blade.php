@extends('layouts.admin')

@section('title', 'Student Directory')

@section('content')
<div class="card">
    <div class="card-header">Students Directory</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Class</th><th>Role</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->assignedClass->full_name ?? 'Unassigned' }}</td>
                        <td>{{ ucfirst($student->role) }}</td>
                        <td class="text-end"><a href="{{ route('student.directory.student', $student) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $students->links() }}
    </div>
</div>
@endsection
