@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Class Management</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary-custom mb-3">
                            <i class="fas fa-plus me-2"></i> Add New Class
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Level</th>
                                    <th>Stream</th>
                                    <th>Class Teacher</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classes as $class)
                                <tr>
                                    <td>{{ $class->name }}</td>
                                    <td>{{ $class->level }}</td>
                                    <td>{{ $class->stream }}</td>
                                    <td>{{ $class->classTeacher->full_name ?? 'Not Assigned' }}</td>
                                    <td>{{ \App\Models\User::where('role', 'student')->whereHas('studentSubject', function($query) use ($class) {
                            return $query->whereIn('subject_id', $class->subjects->pluck('id'));
                        })->count() }}</td>
                                    <td>
                                        @if($class->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary-custom me-1">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
