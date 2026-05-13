@extends('layouts.admin')

@section('title', 'Staff Profile')

@section('content')
<div class="card">
    <div class="card-header">Profile Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route($routePrefix . '.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Login Username</label>
                <input type="text" name="portal_id" class="form-control" value="{{ old('portal_id', $user->portal_id) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
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
