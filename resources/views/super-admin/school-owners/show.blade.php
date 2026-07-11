@extends('super-admin.layout')

@section('title', $schoolOwner->name)
@section('subtitle', 'Owner account details and owned school.')

@section('content')
<div class="row g-3">
    <div class="col-lg-7"><div class="platform-card p-3">
        <h2 class="h5">Owner Profile</h2><hr>
        <div class="row g-3">
            <div class="col-md-6"><strong>Email</strong><div>{{ $schoolOwner->email ?? 'Not set' }}</div></div>
            <div class="col-md-6"><strong>Phone</strong><div>{{ $schoolOwner->phone ?? 'Not set' }}</div></div>
            <div class="col-md-6"><strong>Status</strong><div><span class="status-badge status-{{ $schoolOwner->status ?? 'active' }}">{{ ucfirst($schoolOwner->status ?? 'active') }}</span></div></div>
            <div class="col-md-6"><strong>Registered</strong><div>{{ $schoolOwner->created_at->format('M j, Y') }}</div></div>
        </div>
    </div></div>
    <div class="col-lg-5"><div class="platform-card p-3">
        <h2 class="h5">School</h2><hr>
        @if($schoolOwner->school)
            <strong>{{ $schoolOwner->school->name }}</strong>
            <div class="text-muted mb-2">{{ $schoolOwner->school->plan?->name ?? 'No plan' }}</div>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('super-admin.schools.show', $schoolOwner->school) }}">View school</a>
        @else
            <div class="text-muted">No school attached.</div>
        @endif
        @if(Auth::guard('platform_admin')->user()?->isSuperAdmin())
            <hr><div class="actions-row">
                <a class="btn btn-primary" href="{{ route('super-admin.school-owners.edit', $schoolOwner) }}">Edit owner</a>
                <form method="POST" action="{{ route('super-admin.school-owners.reset-password', $schoolOwner) }}" onsubmit="return confirm('Reset this owner password?')">@csrf<button class="btn btn-outline-secondary">Reset password</button></form>
                <form method="POST" action="{{ route('super-admin.school-owners.destroy', $schoolOwner) }}" onsubmit="return confirm('Delete this school owner account? This does not delete the school.')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Delete owner</button></form>
            </div>
        @endif
    </div></div>
</div>
@endsection
