@extends('owner.app')

@section('title', 'Portal Admins')
@section('page-title', 'Portal Admins')
@section('page-subtitle', 'Create CBT admin accounts for your school portal.')

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <section class="dashboard-card">
            <h2 class="h5">Create admin</h2>
            <p class="text-muted">Your current plan allows {{ $adminLimit }} CBT admin account{{ $adminLimit === 1 ? '' : 's' }}.</p>

            @if(! $tenantReady)
                <div class="alert alert-warning mb-0">
                    Start a free trial or complete payment approval before creating portal admin accounts.
                </div>
            @else
                <form method="POST" action="{{ route('platform.portal-admins.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Portal ID</label>
                        <input class="form-control" name="portal_id" value="{{ old('portal_id') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full name</label>
                        <input class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" @disabled($admins->count() >= $adminLimit)>Create admin</button>
                </form>
            @endif
        </section>
    </div>

    <div class="col-lg-7">
        <section class="dashboard-card">
            <h2 class="h5">Current portal admins</h2>
            <p class="text-muted">{{ $admins->count() }} of {{ $adminLimit }} admin account{{ $adminLimit === 1 ? '' : 's' }} used.</p>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Name</th><th>Portal ID</th><th>Email</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>{{ trim(($admin->first_name ?? '').' '.($admin->last_name ?? '')) ?: 'Admin' }}</td>
                            <td>{{ $admin->portal_id }}</td>
                            <td>{{ $admin->email ?: 'No email' }}</td>
                            <td>
                                @if($admin->email === $owner->email || $admin->portal_id === $owner->email)
                                    <span class="text-muted small">Primary owner</span>
                                @else
                                    <form method="POST" action="{{ route('platform.portal-admins.destroy', $admin->id) }}" onsubmit="return confirm('Delete this portal admin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No portal admins found yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
