@extends('super-admin.layout')

@section('title', 'Payment Dispute')
@section('subtitle', $paymentDispute->reference)

@section('content')
<div class="platform-card p-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h2 class="h5 mb-1">{{ $paymentDispute->subject }}</h2>
            <div class="text-muted">{{ $paymentDispute->school?->name ?? 'No school' }} · {{ $paymentDispute->owner?->name ?? 'No owner' }}</div>
        </div>
        <div class="actions-row">
            @foreach(['investigating' => 'Investigating', 'resolved' => 'Resolve', 'rejected' => 'Reject', 'closed' => 'Close'] as $status => $label)
                <form method="POST" action="{{ route('super-admin.payment-disputes.mark', [$paymentDispute, $status]) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-sm btn-outline-primary">{{ $label }}</button>
                </form>
            @endforeach
            <a class="btn btn-sm btn-primary" href="{{ route('super-admin.payment-disputes.edit', $paymentDispute) }}">Edit</a>
        </div>
    </div>
    <hr>
    <div class="row g-3">
        <div class="col-md-3"><strong>Status</strong><div><span class="status-badge status-{{ $paymentDispute->status }}">{{ ucfirst($paymentDispute->status) }}</span></div></div>
        <div class="col-md-3"><strong>Priority</strong><div><span class="status-badge status-{{ $paymentDispute->priority }}">{{ ucfirst($paymentDispute->priority) }}</span></div></div>
        <div class="col-md-3"><strong>Amount</strong><div>{{ $paymentDispute->disputed_amount ? 'NGN '.number_format($paymentDispute->disputed_amount, 2) : 'Not set' }}</div></div>
        <div class="col-md-3"><strong>Assigned</strong><div>{{ $paymentDispute->assignedAdmin?->name ?? 'Unassigned' }}</div></div>
        <div class="col-md-6"><strong>Payment</strong><div>{{ $paymentDispute->payment?->payment_reference ?? 'Not linked' }}</div></div>
        <div class="col-md-6"><strong>Resolved</strong><div>{{ $paymentDispute->resolved_at?->format('M j, Y g:i A') ?? 'Not resolved' }}</div></div>
        <div class="col-md-6"><strong>Description</strong><div class="text-muted">{{ $paymentDispute->description }}</div></div>
        <div class="col-md-6"><strong>Finance notes</strong><div class="text-muted">{{ $paymentDispute->finance_notes ?? 'No notes yet.' }}</div></div>
    </div>
</div>
@endsection
