@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New Class</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.classes.store') }}">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Class Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="level" class="form-label">Level</label>
                                <select class="form-control" id="level" name="level" required>
                                    <option value="">Select Level</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}">{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stream" class="form-label">Stream</label>
                                <select class="form-control" id="stream" name="stream">
                                    <option value="">Select Stream</option>
                                    @foreach($streams as $stream)
                                        <option value="{{ $stream }}">{{ $stream }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="class_teacher_id" class="form-label">Class Teacher</label>
                                <select class="form-control" id="class_teacher_id" name="class_teacher_id">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom">Create Class</button>
                            <a href="{{ route('admin.classes') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
