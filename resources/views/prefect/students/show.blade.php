@extends('layouts.admin')

@section('title', 'Student Profile')

@section('content')
<div class="card">
    <div class="card-header">{{ $student->full_name }}</div>
    <div class="card-body">
        <p><strong>Class:</strong> {{ $student->assignedClass->full_name ?? 'Unassigned' }}</p>
        <p><strong>Role:</strong> {{ ucfirst($student->role) }}</p>
        <p><strong>Subjects:</strong> {{ $student->subjects->pluck('name')->join(', ') ?: 'None' }}</p>
        <a href="{{ route('prefect.students.edit', $student) }}" class="btn btn-primary">Edit Student</a>
    </div>
</div>
@endsection
