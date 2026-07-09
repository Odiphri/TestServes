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
    <div class="owner-card-actions"><button class="btn btn-primary">Save profile</button></div>
</form>
@endsection
