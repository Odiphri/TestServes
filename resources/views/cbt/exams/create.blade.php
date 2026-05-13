@extends('layouts.admin')

@section('title', 'Create Exam')

@section('content')
<div class="card">
    <div class="card-header">Create Exam</div>
    <div class="card-body">
        <form method="POST" action="{{ route($routePrefix . '.exams.store') }}">
            @csrf
            @include('cbt.exams.partials.form')
            <div class="d-flex justify-content-between gap-2">
                <a href="{{ route($routePrefix . '.exams') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Exam</button>
            </div>
        </form>
    </div>
</div>
@endsection
