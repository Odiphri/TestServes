@extends('layouts.admin')

@section('title', 'Edit Student')

@section('content')
<div class="card">
    <div class="card-header">Edit {{ $student->full_name }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('prefect.students.update', $student) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Class</label>
                <select name="school_class_id" class="form-control">
                    <option value="">Unassigned</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id', $student->school_class_id) == $class->id)>{{ $class->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Subjects</label>
                <select name="subject_ids[]" class="form-control" multiple>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(in_array($subject->id, old('subject_ids', $student->subjects->pluck('id')->all())))>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Save Student</button>
        </form>
    </div>
</div>
@endsection
