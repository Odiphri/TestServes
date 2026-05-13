@extends('layouts.admin')

@section('title', $exam->title)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>{{ $exam->title }}</span>
        <div>
            <a href="{{ route($routePrefix . '.exams.edit', $exam) }}" class="btn btn-light btn-sm">Edit</a>
            <form method="POST" action="{{ route($routePrefix . '.exams.toggle', $exam) }}" class="d-inline">
                @csrf
                <button class="btn btn-light btn-sm">{{ $exam->is_live ? 'Take Offline' : 'Put Live' }}</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <p><strong>Class:</strong> {{ $exam->schoolClass->full_name ?? 'N/A' }}</p>
        <p><strong>Subject:</strong> {{ $exam->subject->name ?? 'N/A' }}</p>
        <p><strong>Duration:</strong> {{ $exam->duration_minutes }} minutes</p>

        <form method="POST" action="{{ route($routePrefix . '.exams.generate-questions', $exam) }}" class="row g-2 mb-4">
            @csrf
            <div class="col-md-7">
                <input type="text" name="topic" class="form-control" placeholder="Topic for draft AI questions" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="number_of_questions" class="form-control" value="5" min="1" max="20" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">AI Set Questions</button>
            </div>
        </form>

        <h5>Questions</h5>
        @forelse($exam->questions as $question)
            <div class="border rounded p-3 mb-2">
                <strong>{{ $loop->iteration }}.</strong>
                <div>{!! $question->question_text !!}</div>
                <div>A. {{ $question->option_a }}</div>
                <div>B. {{ $question->option_b }}</div>
                <div>C. {{ $question->option_c }}</div>
                <div>D. {{ $question->option_d }}</div>
                <small>Correct: {{ $question->correct_answer }}</small>
            </div>
        @empty
            <p class="text-muted">No questions yet.</p>
        @endforelse
    </div>
</div>
@endsection
