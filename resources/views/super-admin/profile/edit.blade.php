@extends('super-admin.layout')

@section('title', 'My Profile')
@section('subtitle', 'Update your platform admin details and profile picture.')

@section('content')
<form class="platform-card p-3" method="POST" action="{{ route('super-admin.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="d-flex align-items-center gap-3 mb-4">
        <img src="{{ $admin->profile_picture_url ?? asset('images/default-avatar.svg') }}" alt="{{ $admin->name }}" onerror="this.src='{{ asset('images/default-avatar.svg') }}'" style="width:72px;height:72px;border-radius:50%;object-fit:cover;background:#e2e8f0;">
        <div>
            <h2 class="h5 mb-1">{{ $admin->name }}</h2>
            <div class="text-muted">{{ ucwords(str_replace('_', ' ', $admin->role)) }}</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $admin->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $admin->email) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" value="{{ old('phone', $admin->phone) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Profile picture</label>
            <input class="form-control" type="file" name="profile_picture" accept="image/*">
        </div>
        @if($admin->profile_picture)
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remove_profile_picture" value="1" id="remove_profile_picture">
                    <label class="form-check-label" for="remove_profile_picture">Remove current profile picture</label>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Save profile</button>
        <a class="btn btn-outline-secondary" href="{{ route('super-admin.dashboard') }}">Back to dashboard</a>
    </div>
</form>
@endsection
