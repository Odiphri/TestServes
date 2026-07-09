@extends('owner.app')
@section('title', 'School')
@section('page-title', 'School')
@section('page-subtitle', 'Edit school identity and contact details.')
@section('content')
<form class="dashboard-card" action="{{ route('platform.school.update') }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">School name</label><input class="form-control" name="school_name" value="{{ old('school_name', $school?->name) }}"></div>
        <div class="col-md-6"><label class="form-label">Portal name</label><input class="form-control" name="school_slug" value="{{ old('school_slug', $school?->slug) }}"></div>
        <div class="col-md-6"><label class="form-label">School type</label><select class="form-select" name="school_type"><option value="">Choose later</option>@foreach(['Nursery', 'Primary', 'Secondary', 'Combined'] as $type)<option value="{{ $type }}" @selected(old('school_type', $school?->school_type) === $type)>{{ $type }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Expected students</label><input class="form-control" type="number" min="1" name="expected_students_count" value="{{ old('expected_students_count', $school?->expected_students_count) }}"></div>
        <div class="col-md-6"><label class="form-label">Contact email</label><input class="form-control" type="email" name="contact_email" value="{{ old('contact_email', $school?->contact_email ?: $owner->email) }}"></div>
        <div class="col-md-6"><label class="form-label">Contact phone</label><input class="form-control" name="contact_phone" value="{{ old('contact_phone', $school?->contact_phone ?: $owner->phone) }}"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="school_address" rows="3">{{ old('school_address', $school?->address) }}</textarea></div>
    </div>
    <div class="owner-card-actions"><button class="btn btn-primary">Save school</button></div>
</form>
@endsection
