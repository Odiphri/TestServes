@extends('layouts.admin')

@section('title', 'Student Roles')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the highlighted fields.</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    @if($canEdit)
        <div class="col-lg-4">
            <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-student-role-panel" aria-expanded="false">
                <i class="fas fa-plus me-2"></i>Create Role
            </button>
            <div class="card collapse d-lg-block {{ $errors->any() ? 'show' : '' }}" id="create-student-role-panel">
                <div class="card-header">Add Student Role</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('student-roles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Class Captain" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active" checked>
                            <label class="form-check-label" for="is-active">Active</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                            <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-student-role-panel">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">Create Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $canEdit ? 'col-lg-8' : 'col-12' }}">
        <div class="card">
            <div class="card-header">Student Roles</div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Students</th>
                            <th>Status</th>
                            @if($canEdit)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentRoles as $studentRole)
                            <tr>
                                @if($canEdit)
                                    <form method="POST" action="{{ route('student-roles.update', $studentRole) }}">
                                        @csrf
                                        @method('PUT')
                                        <td><input type="text" name="name" class="form-control" value="{{ $studentRole->name }}" required></td>
                                        <td><input type="text" name="description" class="form-control" value="{{ $studentRole->description }}"></td>
                                        <td>{{ $studentRole->students_count }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($studentRole->is_active)>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary-custom">Save</button>
                                                <button class="btn btn-outline-danger" type="submit" form="delete-student-role-{{ $studentRole->id }}" onclick="return confirm('Delete this class role? Students using it will keep their account but lose this role.')">Delete</button>
                                            </div>
                                        </td>
                                    </form>
                                @else
                                    <td>{{ $studentRole->name }}</td>
                                    <td>{{ $studentRole->description ?: 'No description' }}</td>
                                    <td>{{ $studentRole->students_count }}</td>
                                    <td>
                                        <span class="badge {{ $studentRole->is_active ? 'bg-success' : 'bg-danger' }} text-white">
                                            {{ $studentRole->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canEdit ? 5 : 4 }}" class="text-muted">No student roles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if($canEdit)
                    @foreach($studentRoles as $studentRole)
                        <form id="delete-student-role-{{ $studentRole->id }}" method="POST" action="{{ route('student-roles.destroy', $studentRole) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif

                {{ $studentRoles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
