@extends('super-admin.layout')
@section('title', 'Payment Record')
@section('subtitle', 'Manual payment details and subscription period.')
@section('content')
<div class="platform-card p-3">
    <div class="d-flex justify-content-between"><h2 class="h5">{{ $payment->payment_reference ?? 'No reference' }}</h2><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></div><hr>
    <div class="row g-3">
        <div class="col-md-4"><strong>School</strong><div>{{ $payment->school?->name ?? 'Not set' }}</div></div>
        <div class="col-md-4"><strong>Owner</strong><div>{{ $payment->owner?->name ?? $payment->school?->owner?->name ?? 'Not set' }}</div></div>
        <div class="col-md-4"><strong>Plan</strong><div>{{ $payment->plan?->name ?? 'No plan' }}</div></div>
        <div class="col-md-4"><strong>Amount</strong><div>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div></div>
        <div class="col-md-4"><strong>Method</strong><div>{{ ucwords(str_replace('_',' ', $payment->payment_method)) }}</div></div>
        <div class="col-md-4"><strong>Payment date</strong><div>{{ optional($payment->payment_date)->format('M j, Y') ?? 'Not set' }}</div></div>
        <div class="col-md-4"><strong>Evidence</strong><div>@if($payment->evidence_url)<a href="{{ $payment->evidence_url }}" target="_blank" rel="noopener">Open screenshot/receipt</a>@else<span class="text-muted">No evidence uploaded</span>@endif</div></div>
        <div class="col-12"><strong>Notes</strong><div class="text-muted">{{ $payment->notes ?? 'No notes' }}</div></div>
    </div>
    <hr>
    <div class="actions-row">
        <a class="btn btn-primary" href="{{ route('super-admin.payments.edit', $payment) }}">Edit payment</a>
        @if(Auth::guard('platform_admin')->user()?->canAccessPlatformSection('payment_disputes'))
            <a class="btn btn-outline-danger" href="{{ route('super-admin.payment-disputes.create', ['payment_record_id' => $payment->id]) }}">Open dispute</a>
        @endif
    </div>
</div>
@endsection
