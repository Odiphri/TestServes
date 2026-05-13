@extends('layouts.admin')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->full_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <form method="POST" action="{{ route('admin.users.role.update', $user) }}">
                                        @csrf
                                        @method('PUT')
                                        <td>
                                            <select name="role" class="form-select form-select-sm">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role }}" @selected($user->role === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($user->is_active)>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary-custom">Save</button>
                                        </td>
                                    </form>
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
