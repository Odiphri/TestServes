@extends('layouts.admin')

@section('title', 'My Classes')

@section('content')
<div class="card">
    <div class="card-header">Assigned Classes</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Class</th><th>Subjects</th><th>Class Teacher</th></tr></thead>
            <tbody>
                @forelse($classes as $class)
                    <tr>
                        <td>{{ $class->full_name }}</td>
                        <td>{{ $class->subjects->pluck('name')->join(', ') ?: 'None' }}</td>
                        <td>{{ $class->classTeacher->full_name ?? 'Unassigned' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No assigned classes found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $classes->links() }}
    </div>
</div>
@endsection
