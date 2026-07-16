@extends('owner.app')

@section('title', 'Owner Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview only. Use the sidebar pages to make changes.')

@section('content')
@php
    $dueAt = $lifecycle['due_at'] ?? null;
    $deactivationAt = $lifecycle['deactivation_at'] ?? null;
    $daysUntilDue = $lifecycle['days_until_due'] ?? null;
    $daysUntilDeactivation = $lifecycle['days_until_deactivation'] ?? null;
    $hasPaidBefore = $lifecycle['has_paid_before'] ?? false;
    $dayLabel = function ($days, $futureLabel = 'left') {
        if ($days === null) {
            return '';
        }

        if ($days < 0) {
            return ' ('.abs($days).' day'.(abs($days) === 1 ? '' : 's').' overdue)';
        }

        return ' ('.$days.' day'.($days === 1 ? '' : 's').' '.$futureLabel.')';
    };
@endphp
<section class="cockpit-hero mb-3">
    <div>
        <span class="owner-eyebrow">Workspace</span>
        <h2>{{ $school?->name ?? 'School workspace' }}</h2>
        <p>{{ $school?->hasPortalAccess() ? 'Your school portal is open.' : 'Your portal is locked until trial starts or payment is confirmed by Finance Admin.' }}</p>
        <div class="cockpit-actions">
            @if($school?->hasPortalAccess() && $school?->portal_url)
                <a class="btn btn-primary" href="{{ $school->portal_url }}/login" target="_blank" rel="noopener">Open school portal</a>
                <a class="btn btn-outline-light" href="{{ route('platform.portal-admins') }}">Create portal admin</a>
            @else
                @if($school?->subscription_plan_id)
                    <form action="{{ route('platform.trial.start') }}" method="POST">
                        @csrf
                        <button class="btn btn-primary">Start free trial</button>
                    </form>
                @endif
                <a class="btn btn-primary" href="{{ route('platform.payments') }}">Make payment</a>
            @endif
            <a class="btn btn-outline-light" href="{{ route('platform.branding') }}">Edit branding</a>
        </div>
    </div>
    <div class="cockpit-badge-card">
        <div class="school-logo-preview">
            <img src="{{ $branding?->logo_url ?? \App\Models\SystemSetting::platformLogoUrl() }}" alt="{{ $school?->name ?? 'School' }} logo" onerror="this.src='{{ \App\Models\SystemSetting::platformLogoUrl() }}'">
        </div>
        <strong>{{ $school?->hasPortalAccess() ? 'Open' : 'Locked' }}</strong>
        <span>Portal access</span>
    </div>
</section>

@if($school)
    <section class="dashboard-card subscription-timeline mb-3">
        <div>
            <span class="card-kicker">Subscription timeline</span>
            <h3>{{ $school->status === 'deactivated' ? 'Portal deactivated' : 'Next payment and deactivation window' }}</h3>
            <p>
                @if($school->status === 'deactivated')
                    {{ $school->deactivation_reason ?? 'Your school portal is deactivated. Renew or contact support to restore access.' }}
                @elseif($dueAt)
                    Payment is due {{ $dueAt->format('M j, Y') }}{{ $dayLabel($daysUntilDue) }}.
                    @if($deactivationAt)
                        Portal deactivation is scheduled for {{ $deactivationAt->format('M j, Y') }}{{ $dayLabel($daysUntilDeactivation) }}.
                    @endif
                @else
                    Choose a plan, start a trial, or submit payment to activate your school portal.
                @endif
            </p>
        </div>
        <div class="subscription-timeline-actions">
            <a class="btn btn-primary" href="{{ route('platform.payments') }}">{{ $hasPaidBefore ? 'Renew subscription' : 'Make payment' }}</a>
            <a class="btn btn-outline-secondary" href="{{ route('platform.plans') }}">Upgrade or downgrade</a>
        </div>
    </section>
@endif

<div class="cockpit-grid mb-3">
    <section class="dashboard-card">
        <span class="card-kicker">Plan</span>
        <h3>{{ $school?->plan?->name ?? 'No plan selected' }}</h3>
        <p>{{ $school?->plan ? 'NGN '.number_format($school->plan->monthly_price, 0).'/month' : 'Choose a plan from the Plans page.' }}</p>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('platform.plans') }}">Plans</a>
    </section>
    <section class="dashboard-card">
        <span class="card-kicker">Payment</span>
        <h3>{{ ucfirst($subscription?->status ?? 'pending') }}</h3>
        <p>{{ $school?->hasPortalAccess() ? 'Portal access is available.' : 'Start a trial or submit payment and wait for confirmation.' }}</p>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('platform.payments') }}">Payments</a>
    </section>
    <section class="dashboard-card">
        <span class="card-kicker">Portal</span>
        <h3>{{ $school?->slug ? $school->slug.'.'.config('testserves.root_domain') : 'Not set' }}</h3>
        <p>{{ $school?->hasPortalAccess() ? 'Create CBT admin accounts, then school users login from the subdomain.' : 'School users login from the subdomain after trial/payment activation.' }}</p>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('platform.school') }}">School settings</a>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('platform.portal-admins') }}">Portal admins</a>
    </section>
</div>

<section class="dashboard-card">
    <span class="card-kicker">Recent payments</span>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Reference</th><th>Plan</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_reference }}</td>
                    <td>{{ $payment->plan?->name ?? 'No plan' }}</td>
                    <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                    <td><span class="status-pill">{{ ucfirst($payment->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">No payments submitted yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
