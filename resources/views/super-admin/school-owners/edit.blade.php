@extends('super-admin.layout')

@section('title', 'Edit Owner')
@section('subtitle', 'Update a school owner profile without exposing passwords.')

@section('content')
<form method="POST" action="{{ route('super-admin.school-owners.update', $schoolOwner) }}">
    @csrf @method('PUT')
    <div class="platform-card p-3"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $schoolOwner->name) }}" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $schoolOwner->email) }}"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $schoolOwner->phone) }}"></div>
        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">@foreach(['active','disabled','pending'] as $status)<option value="{{ $status }}" @selected(old('status', $schoolOwner->status)===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    </div></div>
    <div class="mt-3"><button class="btn btn-primary">Save owner</button> <a class="btn btn-outline-secondary" href="{{ route('super-admin.school-owners.show', $schoolOwner) }}">Cancel</a></div>
</form>
@endsection
