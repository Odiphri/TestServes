@extends('layouts.admin')

@section('title', 'Students')

@section('content')
<div class="card">
    <div class="card-header">Student Management</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Class</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->assignedClass->full_name ?? 'Unassigned' }}</td>
                        <td class="text-end">
                            <a href="{{ route('prefect.students.show', $student) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('prefect.students.edit', $student) }}" class="btn btn-sm btn-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $students->links() }}
    </div>
</div>
@endsection
