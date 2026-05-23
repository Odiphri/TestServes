@extends('layouts.admin')

@section('title', 'Promote / Demote Students')

@section('content')
<div class="card">
    <div class="card-header">Assigned Class Review</div>
    <div class="card-body">
        @if($assignedClasses->isNotEmpty())
            <div class="mb-3 d-flex flex-wrap gap-2">
                @foreach($assignedClasses as $class)
                    <span class="badge bg-secondary">{{ $class->full_name }}</span>
                @endforeach
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Current Class</th>
                        <th>Demote To</th>
                        <th>Promote To</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $previousClass = $promotionService->previousClass($student->assignedClass);
                            $nextClass = $promotionService->nextClass($student->assignedClass);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $student->full_name }}</strong>
                                <div class="text-muted small">{{ $student->portal_id }}</div>
                            </td>
                            <td>{{ $student->assignedClass->full_name ?? 'Unassigned' }}</td>
                            <td>{{ $previousClass?->full_name ?? 'N/A' }}</td>
                            <td>{{ $nextClass?->full_name ?? 'N/A' }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('teacher.promotions.demote', $student) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" @disabled(!$previousClass)>Demote</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.promotions.promote', $student) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-primary-custom" @disabled(!$nextClass)>Promote</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No students found in your assigned classes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $students->links() }}
    </div>
</div>
@endsection
