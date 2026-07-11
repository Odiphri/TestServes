@extends('super-admin.layout')

@section('title', 'Super Admin Dashboard')
@section('subtitle', 'Platform-wide schools, subscriptions, owners, and revenue overview.')

@section('content')
<section class="kpi-grid">
    @foreach([
        ['Total schools', $stats['total_schools']],
        ['Active schools', $stats['active_schools']],
        ['Pending schools', $stats['pending_schools']],
        ['Suspended schools', $stats['suspended_schools']],
        ['Trial schools', $stats['trial_schools']],
        ['Expired schools', $stats['expired_schools']],
        ['Archived schools', $stats['deleted_schools']],
        ['School owners', $stats['total_school_owners']],
        ['Subscription plans', $stats['total_subscription_plans']],
        ['Total payments', $stats['total_payments']],
        ['Pending payments', $stats['pending_payments']],
        ['Confirmed payments', $stats['confirmed_payments']],
        ['Monthly revenue', 'NGN '.number_format($stats['monthly_revenue'], 2)],
        ['Yearly revenue', 'NGN '.number_format($stats['yearly_revenue'], 2)],
        ['Expiring soon', $stats['expiring_subscriptions']],
    ] as [$label, $value])
        <div class="platform-card kpi">
            <span>{{ $label }}</span>
            <strong>{{ $value }}</strong>
        </div>
    @endforeach
</section>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="platform-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent Registered Schools</h2>
                <a href="{{ route('super-admin.schools.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>School</th><th>Owner</th><th>Plan</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentSchools as $school)
                            <tr>
                                <td><a href="{{ route('super-admin.schools.show', $school) }}">{{ $school->name }}</a><div class="small text-muted">{{ $school->slug }}</div></td>
                                <td>{{ $school->owner?->name ?? 'Not assigned' }}</td>
                                <td>{{ $school->plan?->name ?? 'No plan' }}</td>
                                <td><span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No schools registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Recent Payments</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>School</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->school?->name ?? 'Unknown school' }}<div class="small text-muted">{{ $payment->plan?->name ?? 'Plan not set' }}</div></td>
                                <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                <td><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No platform payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Recent Owners</h2>
            @forelse($recentOwners as $owner)
                <div class="border-bottom py-2"><strong>{{ $owner->name }}</strong><div class="small text-muted">{{ $owner->school?->name ?? 'No school' }} · {{ $owner->created_at->format('M j, Y') }}</div></div>
            @empty
                <div class="text-muted">No owners yet.</div>
            @endforelse
        </div>
    </div>
    <div class="col-lg-8">
        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Support Tickets</h2>
            @forelse($recentSupportTickets as $ticket)
                <div class="border-bottom py-2"><strong>{{ $ticket->subject }}</strong><div class="small text-muted">{{ $ticket->school?->name ?? 'No school' }} · {{ ucfirst(str_replace('_',' ', $ticket->status)) }}</div></div>
            @empty
                <div class="text-muted">No support tickets yet.</div>
            @endforelse
        </div>
    </div>
    <div class="col-12">
        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Recent Activity</h2>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Actor</th><th>Action</th><th>Description</th><th>Date</th></tr></thead><tbody>
                @forelse($recentLogs as $log)
                    <tr><td>{{ $log->actor?->name ?? 'System' }}</td><td><code>{{ $log->action }}</code></td><td>{{ $log->description }}</td><td>{{ $log->created_at->format('M j, Y g:ia') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No platform activity recorded yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
</div>
@endsection
