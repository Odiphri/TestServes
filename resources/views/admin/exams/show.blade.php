@extends('layouts.admin')

@section('title', $exam->title)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $exam->title }}</span>
        <div>
            <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-light btn-sm">Edit</a>
            <a href="{{ route('admin.exams') }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3"><strong>Class:</strong> {{ $exam->target_class_names ?: ($exam->schoolClass->full_name ?? 'N/A') }}</div>
            <div class="col-md-3"><strong>Subject:</strong> {{ $exam->subject->name ?? 'N/A' }}</div>
            <div class="col-md-3"><strong>Teacher:</strong> {{ $exam->creator->full_name ?? 'N/A' }}</div>
            <div class="col-md-3"><strong>Duration:</strong> {{ $exam->duration_minutes }} minutes</div>
        </div>

        <div class="mb-4">
            <span class="badge {{ $exam->is_live ? 'bg-success' : 'bg-secondary' }}">
                {{ $exam->is_live ? 'Live' : 'Offline' }}
            </span>
            <span class="badge {{ $exam->show_results ? 'bg-info' : 'bg-secondary' }}">
                {{ $exam->show_results ? 'Results Visible' : 'Results Hidden' }}
            </span>
        </div>

        <h5>Questions</h5>
        @forelse($exam->questions as $question)
            <div class="border rounded p-3 mb-2">
                <strong>{{ $loop->iteration }}.</strong>
                <div>{!! $question->question_text !!}</div>
                <div>A. {{ $question->option_a }}</div>
                <div>B. {{ $question->option_b }}</div>
                <div>C. {{ $question->option_c }}</div>
                <div>D. {{ $question->option_d }}</div>
                <small>Correct: {{ $question->correct_answer }} | Points: {{ $question->points }}</small>
            </div>
        @empty
            <p class="text-muted">No questions yet.</p>
        @endforelse
    </div>
</div>
@endsection
