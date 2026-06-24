@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Create Admin User</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" class="user-create-form">
                    @csrf
                    <div>
                        <label class="form-label">Admin ID</label>
                        <input type="text" name="portal_id" class="form-control" value="{{ old('portal_id') }}" placeholder="e.g. ADM001" required>
                    </div>
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Jane Admin" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Optional">
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="text" name="password" class="form-control" value="{{ old('password') }}" required>
                    </div>
                    <div class="d-grid align-self-end">
                        <button class="btn btn-primary-custom">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">User Management</div>
            <div class="card-body">
                <x-live-search
                    :action="route('admin.users')"
                    target="users-results"
                    :search="$search ?? ''"
                    placeholder="Name, portal ID, or email"
                    :clear-href="route('admin.users')"
                >
                    <div class="col-12 col-md-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">All roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(($selectedRole ?? '') === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </x-live-search>

                <div id="users-results" aria-live="polite">
                <div class="d-none d-lg-block table-responsive">
                    <table class="table table-striped table-hover align-middle user-management-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->full_name }}</strong>
                                        <div class="small text-muted">{{ $user->portal_id }}</div>
                                    </td>
                                    <td>{{ $user->email ?: 'No email' }}</td>
                                    <td colspan="3" class="p-0">
                                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="user-row-form">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <select name="role" class="form-select form-select-sm">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role }}" @selected($user->role === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="user-active-{{ $user->id }}" @checked($user->is_active)>
                                                    <label class="form-check-label" for="user-active-{{ $user->id }}">Active</label>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button class="btn btn-sm btn-primary-custom">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-lg-none user-mobile-list">
                    @forelse($users as $user)
                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="user-mobile-card">
                            @csrf
                            @method('PUT')
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="min-w-0">
                                    <div class="user-mobile-title">{{ $user->full_name }}</div>
                                    <div class="user-mobile-subtitle">{{ $user->portal_id }}{{ $user->email ? ' - ' . $user->email : '' }}</div>
                                </div>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select">
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" @selected($user->role === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="mobile-user-active-{{ $user->id }}" @checked($user->is_active)>
                                        <label class="form-check-label" for="mobile-user-active-{{ $user->id }}">Active</label>
                                    </div>
                                </div>
                                <div class="col-6 d-grid">
                                    <button class="btn btn-primary-custom">Save</button>
                                </div>
                            </div>
                        </form>
                    @empty
                        <div class="text-muted py-4">No users found.</div>
                    @endforelse
                </div>

                {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-create-form {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr)) minmax(110px, auto);
    gap: 12px;
    align-items: end;
}

.user-management-table {
    table-layout: fixed;
}

.user-management-table th:nth-child(1) { width: 27%; }
.user-management-table th:nth-child(2) { width: 27%; }
.user-management-table th:nth-child(3) { width: 20%; }
.user-management-table th:nth-child(4) { width: 13%; }
.user-management-table th:nth-child(5) { width: 13%; }

.user-row-form {
    display: grid;
    grid-template-columns: minmax(170px, 1fr) minmax(105px, auto) minmax(90px, auto);
    gap: 12px;
    align-items: center;
    padding: .75rem;
}

.user-mobile-list {
    display: grid;
    gap: 12px;
}

.user-mobile-card {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}

.user-mobile-title {
    color: #0a1931;
    font-weight: 700;
    font-size: 1rem;
}

.user-mobile-subtitle {
    color: #6c757d;
    font-size: .85rem;
    overflow-wrap: anywhere;
    margin-top: 2px;
}

@media (max-width: 992px) {
    .user-create-form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 576px) {
    .user-create-form {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
