@extends('layouts.admin')

@section('title', 'HOD Students')

@section('content')
<div class="card">
    <div class="card-header">Student Academic Records</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Subjects</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->full_name }}</td>
                            <td>{{ $student->assignedClass->full_name ?? 'Unassigned' }}</td>
                            <td>{{ $student->subjects->pluck('name')->join(', ') ?: 'None' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $students->links() }}
    </div>
</div>
@endsection
