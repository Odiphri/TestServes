@extends('layouts.admin')

@section('title', 'Update Bursary')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            {{ $student->full_name }}
            <span class="badge bg-secondary ms-2">{{ $student->assignedClass->full_name ?? 'No class' }}</span>
        </div>
        <span class="badge {{ $summary['balance'] > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
            {{ $summary['paid_percent'] }}% paid
        </span>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3 text-center">
            <div class="col-md-3"><strong>N{{ number_format($summary['total_due'], 2) }}</strong><br><small class="text-muted">To Pay</small></div>
            <div class="col-md-3"><strong>N{{ number_format($summary['amount_paid'], 2) }}</strong><br><small class="text-muted">Paid</small></div>
            <div class="col-md-3"><strong>N{{ number_format($summary['balance'], 2) }}</strong><br><small class="text-muted">Balance</small></div>
            <div class="col-md-3"><strong>{{ $summary['unpaid_percent'] }}%</strong><br><small class="text-muted">To Be Paid</small></div>
        </div>

        <div class="progress mb-4" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: {{ $summary['paid_percent'] }}%"></div>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.payments.students.update', $student) }}" class="row g-2 align-items-end mb-4">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label">Amount Paid So Far</label>
                <input type="number" name="amount_paid" class="form-control" min="0" step="0.01" value="{{ old('amount_paid', $summary['amount_paid']) }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Payment Note</label>
                <input type="text" name="payment_details" class="form-control" value="{{ old('payment_details', $summary['payment']->payment_details ?? '') }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary-custom w-100">Update Payment</button>
            </div>
        </form>

        <div class="fw-semibold mb-2">Optional Fees</div>
        @forelse($summary['optional_fees'] as $fee)
            @php($removed = in_array($fee->id, $summary['removed_fee_ids'], true))
            <div class="d-flex flex-wrap justify-content-between align-items-center border rounded p-2 mb-2 gap-2">
                <div>
                    {{ $fee->name }}
                    <small class="text-muted">N{{ number_format($fee->amount, 2) }}</small>
                    @if($removed)
                        <span class="badge bg-secondary ms-2">Removed</span>
                    @endif
                </div>
                @if($removed)
                    <form method="POST" action="{{ route($routePrefix . '.payments.optional-fees.restore', [$student, $fee]) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-primary">Restore</button>
                    </form>
                @else
                    <form method="POST" action="{{ route($routePrefix . '.payments.optional-fees.remove', [$student, $fee]) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason">
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-muted mb-0">No optional fees apply to this student.</p>
        @endforelse

        <div class="mt-4">
            <a href="{{ route($routePrefix . '.payments') }}" class="btn btn-secondary">Back to Bursary List</a>
        </div>
    </div>
</div>
@endsection
