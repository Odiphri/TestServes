@extends('layouts.admin')

@section('title', 'Edit Exam')

@section('content')
<div class="card">
    <div class="card-header">Edit Exam</div>
    <div class="card-body">
        <form method="POST" action="{{ route($routePrefix . '.exams.update', $exam) }}">
            @csrf
            @method('PUT')
            @include('cbt.exams.partials.form')
            <button type="submit" class="btn btn-primary">Update Exam</button>
        </form>
    </div>
</div>
@endsection
