@extends('layouts.admin')

@section('title', 'Prefect Roles')

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
            <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-prefect-role-panel" aria-expanded="false">
                <i class="fas fa-plus me-2"></i>Create Prefect Role
            </button>
            <div class="card collapse d-lg-block {{ $errors->any() ? 'show' : '' }}" id="create-prefect-role-panel">
                <div class="card-header">Add Prefect Role</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('prefect-roles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Head Boy" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="prefect-role-active" checked>
                            <label class="form-check-label" for="prefect-role-active">Active</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                            <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-prefect-role-panel">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">Create Prefect Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $canEdit ? 'col-lg-8' : 'col-12' }}">
        <div class="card">
            <div class="card-header">Prefect Roles</div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Prefects</th>
                            <th>Status</th>
                            @if($canEdit)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prefectRoles as $prefectRole)
                            <tr>
                                @if($canEdit)
                                    <form method="POST" action="{{ route('prefect-roles.update', $prefectRole) }}">
                                        @csrf
                                        @method('PUT')
                                        <td><input type="text" name="name" class="form-control" value="{{ $prefectRole->name }}" required></td>
                                        <td><input type="text" name="description" class="form-control" value="{{ $prefectRole->description }}"></td>
                                        <td>{{ $prefectRole->prefects_count }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($prefectRole->is_active)>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary-custom">Save</button>
                                                <button class="btn btn-outline-danger" type="submit" form="delete-prefect-role-{{ $prefectRole->id }}" onclick="return confirm('Delete this prefect role? Prefects using it will keep their account but lose this role.')">Delete</button>
                                            </div>
                                        </td>
                                    </form>
                                @else
                                    <td>{{ $prefectRole->name }}</td>
                                    <td>{{ $prefectRole->description ?: 'No description' }}</td>
                                    <td>{{ $prefectRole->prefects_count }}</td>
                                    <td>
                                        <span class="badge {{ $prefectRole->is_active ? 'bg-success' : 'bg-danger' }} text-white">
                                            {{ $prefectRole->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canEdit ? 5 : 4 }}" class="text-muted">No prefect roles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if($canEdit)
                    @foreach($prefectRoles as $prefectRole)
                        <form id="delete-prefect-role-{{ $prefectRole->id }}" method="POST" action="{{ route('prefect-roles.destroy', $prefectRole) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif

                {{ $prefectRoles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
