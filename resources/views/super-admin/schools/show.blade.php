@extends('super-admin.layout')

@section('title', $school->name)
@section('subtitle', 'School profile, portal link, subscription status, owner details, and branding.')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="platform-card p-3 mb-3">
            <div class="d-flex justify-content-between gap-2 align-items-start">
                <div>
                    <h2 class="h4 mb-1">{{ $school->name }}</h2>
                    <div class="text-muted">{{ $school->slug }}</div>
                </div>
                <span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-md-6"><strong>Portal URL</strong><div><a href="{{ $school->portal_url }}" target="_blank" rel="noopener">{{ $school->portal_url }}</a></div></div>
                <div class="col-md-6"><strong>Plan</strong><div>{{ $school->plan?->name ?? 'No plan assigned' }}</div></div>
                <div class="col-md-6"><strong>Subscription start</strong><div>{{ optional($school->subscription_starts_at)->format('M j, Y') ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Subscription expiry</strong><div>{{ optional($school->subscription_expires_at)->format('M j, Y') ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Next payment due</strong><div>{{ optional($school->next_payment_due_at)->format('M j, Y') ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Grace ends</strong><div>{{ optional($school->payment_grace_ends_at)->format('M j, Y') ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Deactivation scheduled</strong><div>{{ optional($school->deactivation_scheduled_at)->format('M j, Y') ?? 'Not scheduled' }}</div></div>
                @if($school->status === 'deactivated')
                    <div class="col-md-6"><strong>Deactivation date</strong><div>{{ optional($school->deactivated_at)->format('M j, Y') ?? 'Not set' }}</div></div>
                    <div class="col-md-6"><strong>Delete notice date</strong><div>{{ optional($school->delete_scheduled_at)->format('M j, Y') ?? 'Not scheduled' }}</div></div>
                    <div class="col-12"><strong>Deactivation reason</strong><div>{{ $school->deactivation_reason ?? 'No reason recorded.' }}</div></div>
                @endif
                <div class="col-md-6"><strong>Contact email</strong><div>{{ $school->contact_email ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Contact phone</strong><div>{{ $school->contact_phone ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>School type</strong><div>{{ $school->school_type ?? 'Not set' }}</div></div>
                <div class="col-md-6"><strong>Expected students</strong><div>{{ $school->expected_students_count ?? 'Not set' }}</div></div>
                <div class="col-12"><strong>Address</strong><div>{{ $school->address ?? 'Not set' }}</div></div>
            </div>
        </div>

        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Owner</h2>
            <div class="row g-3">
                <div class="col-md-4"><strong>Name</strong><div>{{ $school->owner?->name ?? 'Not assigned' }}</div></div>
                <div class="col-md-4"><strong>Email</strong><div>{{ $school->owner?->email ?? 'Not set' }}</div></div>
                <div class="col-md-4"><strong>Phone</strong><div>{{ $school->owner?->phone ?? 'Not set' }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="platform-card p-3 mb-3">
            <h2 class="h5 mb-3">Branding</h2>
            <div class="mb-2"><strong>Display name</strong><div>{{ $school->branding?->portal_display_name ?? $school->name }}</div></div>
            <div class="mb-2"><strong>Short name</strong><div>{{ $school->branding?->short_name ?? 'Not set' }}</div></div>
            <div class="d-flex gap-3 my-3">
                <span title="Primary" class="color-dot" style="background: {{ $school->branding?->primary_color ?? '#2563eb' }}"></span>
                <span title="Secondary" class="color-dot" style="background: {{ $school->branding?->secondary_color ?? '#0f172a' }}"></span>
                <span title="Accent" class="color-dot" style="background: {{ $school->branding?->accent_color ?? '#22c55e' }}"></span>
            </div>
            @if($school->branding?->logo_path)
                <img
                    src="{{ $school->branding->logo_url }}"
                    alt="{{ $school->name }} logo"
                    class="img-fluid rounded border bg-white p-2"
                    style="max-height: 180px; object-fit: contain;"
                    onerror="this.onerror=null; this.src='{{ \App\Models\SystemSetting::platformLogoUrl() }}';"
                >
            @else
                <div class="text-muted">No logo uploaded.</div>
            @endif
        </div>

        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Actions</h2>
            <div class="actions-row">
                @if(Auth::guard('platform_admin')->user()?->isSuperAdmin())
                    <a class="btn btn-primary" href="{{ route('super-admin.schools.edit', $school) }}">Edit school</a>
                    <form action="{{ route('super-admin.schools.reset-owner-password', $school) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-secondary" type="submit">Reset owner password</button>
                    </form>
                    @foreach(['active' => 'Activate', 'suspended' => 'Suspend', 'trial' => 'Mark trial', 'expired' => $school->status === 'trial' ? 'End trial' : 'Mark expired', 'deactivated' => 'Deactivate'] as $status => $label)
                        @continue($school->status === $status)
                        <form action="{{ route('super-admin.schools.status', [$school, $status]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-outline-dark" type="submit">{{ $label }}</button>
                        </form>
                    @endforeach
                    <form action="{{ route('super-admin.schools.destroy', $school) }}" method="POST" onsubmit="return confirm('Delete this school? It will move to Archived schools.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Delete school</button>
                    </form>
                @else
                    <div class="text-muted">You have view-only school access.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
