@extends('layouts.admin')

@section('title', 'Exam Results')

@section('content')
<div class="container">
    <x-live-search
        :action="route(($routePrefix ?? 'teacher') . '.results')"
        target="results-list"
        :search="$search ?? ''"
        placeholder="Exam title or subject"
        :clear-href="route(($routePrefix ?? 'teacher') . '.results')"
    />

    <div class="card">
        <div class="card-header">Exam Results</div>
        <div class="card-body">
            <div id="results-list" aria-live="polite">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Attempts</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam->title }}</td>
                                <td>{{ $exam->subject->name ?? 'N/A' }}</td>
                                <td>{{ $exam->attempts->count() }}</td>
                                <td class="text-end">
                                    <a href="{{ route(($routePrefix ?? 'teacher') . '.results.show', $exam) }}" class="btn btn-sm btn-primary-custom">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No exams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $exams->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
