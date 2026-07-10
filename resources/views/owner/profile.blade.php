@extends('owner.app')
@section('title', 'Profile')
@section('page-title', 'Profile')
@section('page-subtitle', 'Update your account and profile picture.')
@section('content')
<form class="dashboard-card" action="{{ route('platform.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="d-flex align-items-center gap-3 mb-4">
        <img class="owner-avatar" src="{{ $owner->profile_picture_url ?: asset('images/default-avatar.svg') }}" alt="{{ $owner->name }}" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
        <div>
            <h2 class="h5 mb-1">{{ $owner->name }}</h2>
            <p class="text-muted mb-0">Upload a new picture and save; the sidebar will refresh with the new image.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="name" value="{{ old('name', $owner->name) }}" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $owner->email) }}" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $owner->phone) }}"></div>
        <div class="col-md-6"><label class="form-label">Profile picture</label><input class="form-control" type="file" name="profile_picture" accept="image/*"></div>
        @if($owner->profile_picture)<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="remove_profile_picture" value="1"> Remove current picture</label></div>@endif
    </div>

    <hr class="my-4">

    <h3 class="h6 fw-bold mb-2">Change password</h3>
    <p class="text-muted small mb-3">Leave these fields empty if you do not want to change your password.</p>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Current password</label>
            <input class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" autocomplete="current-password">
            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">New password</label>
            <input class="form-control @error('new_password') is-invalid @enderror" type="password" name="new_password" autocomplete="new-password">
            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Confirm new password</label>
            <input class="form-control" type="password" name="new_password_confirmation" autocomplete="new-password">
        </div>
    </div>

    <div class="owner-card-actions"><button class="btn btn-primary">Save profile</button></div>
</form>
@endsection
