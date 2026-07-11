@extends('super-admin.layout')

@section('title', 'Payments')
@section('subtitle', 'Manual payment records only. Paystack automation is intentionally not connected yet.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-3"><label class="form-label">Search reference</label><input class="form-control" name="search" value="{{ request('search') }}"></div>
        <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option>@foreach(['pending','paid','failed','rejected','refunded'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">School</label><select class="form-select" name="school_id"><option value="">All schools</option>@foreach($schools as $school)<option value="{{ $school->id }}" @selected(request('school_id')==$school->id)>{{ $school->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">From</label><input class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
        <div class="col-md-2"><label class="form-label">To</label><input class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-outline-primary">Filter</button><a class="btn btn-primary" href="{{ route('super-admin.payments.create') }}">Create manual payment</a></div>
    </form>
</div>
<div class="platform-card p-3 table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Reference</th><th>School</th><th>Plan</th><th>Amount</th><th>Status</th><th>Evidence</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td><strong>{{ $payment->payment_reference ?? 'No reference' }}</strong><div class="small text-muted">{{ $payment->receipt_number }}</div></td>
                <td>{{ $payment->school?->name ?? 'Not set' }}<div class="small text-muted">{{ $payment->owner?->name ?? $payment->school?->owner?->name }}</div></td>
                <td>{{ $payment->plan?->name ?? 'No plan' }}</td>
                <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                <td><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                <td>
                    @if($payment->evidence_url)
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $payment->evidence_url }}" target="_blank" rel="noopener">Open</a>
                    @else
                        <span class="text-muted small">None</span>
                    @endif
                </td>
                <td>{{ optional($payment->payment_date)->format('M j, Y') ?? 'Not paid' }}</td>
                <td class="actions-row">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.payments.show', $payment) }}">View</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.payments.edit', $payment) }}">Edit</a>
                    @foreach(['paid' => 'Paid', 'failed' => 'Failed', 'rejected' => 'Reject'] as $status => $label)
                        <form method="POST" action="{{ route('super-admin.payments.mark-status', [$payment, $status]) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-dark">{{ $label }}</button></form>
                    @endforeach
                    <form method="POST" action="{{ route('super-admin.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment record?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-muted">No payment records found.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $payments->links() }}
</div>
@endsection
