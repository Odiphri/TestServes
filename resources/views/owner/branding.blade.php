@extends('owner.app')
@section('title', 'Branding')
@section('page-title', 'Branding')
@section('page-subtitle', 'Logo and school portal colors.')
@section('content')
<form class="dashboard-card" action="{{ route('platform.branding.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Display name</label><input class="form-control" name="portal_display_name" value="{{ old('portal_display_name', $branding?->portal_display_name) }}"></div>
        <div class="col-md-6"><label class="form-label">Short name</label><input class="form-control" name="short_name" value="{{ old('short_name', $branding?->short_name) }}"></div>
        <div class="col-md-4"><label class="form-label">Primary</label><input class="form-control form-control-color w-100" type="color" name="primary_color" value="{{ old('primary_color', $branding?->primary_color ?? '#2563eb') }}"></div>
        <div class="col-md-4"><label class="form-label">Secondary</label><input class="form-control form-control-color w-100" type="color" name="secondary_color" value="{{ old('secondary_color', $branding?->secondary_color ?? '#0f172a') }}"></div>
        <div class="col-md-4"><label class="form-label">Accent</label><input class="form-control form-control-color w-100" type="color" name="accent_color" value="{{ old('accent_color', $branding?->accent_color ?? '#22c55e') }}"></div>
        <div class="col-12"><label class="form-label">Logo</label><input class="form-control" type="file" name="logo" accept="image/*"></div>
        @if($branding?->logo_path)<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="remove_logo" value="1"> Remove current logo</label></div>@endif
    </div>
    <div class="owner-card-actions"><button class="btn btn-primary">Save branding</button></div>
</form>
@endsection
