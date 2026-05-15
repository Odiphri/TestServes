@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div class="card">
    <div class="card-header">Profile</div>
    <div class="card-body">
        <p><strong>Name:</strong> {{ $user->full_name }}</p>
        <p><strong>Class:</strong> {{ $user->assignedClass->full_name ?? 'Unassigned' }}</p>
        <p><strong>Subjects:</strong> {{ $user->subjects->pluck('name')->join(', ') ?: 'None' }}</p>

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="{{ $user->profile?->profile_picture_url ?? asset('images/default-avatar.svg') }}" alt="{{ $user->full_name }}" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                <div class="flex-grow-1">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->profile->phone ?? '') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $user->profile->address ?? '') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <button class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</div>
@endsection
