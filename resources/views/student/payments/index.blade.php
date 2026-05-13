@extends('layouts.admin')

@section('title', 'My Payments')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="mb-0">Payment History</h5>
        <small class="text-muted">Track your fee payments and balance</small>
    </div>
    <div class="card-body">
        <!-- Payment Summary -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="card text-center bg-primary text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">₦{{ number_format($totalFees, 2) }}</div>
                        <div class="stat-label">Total Fees</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card text-center bg-success text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">₦{{ number_format($paidAmount, 2) }}</div>
                        <div class="stat-label">Paid Amount</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card text-center {{ $balance > 0 ? 'bg-danger' : 'bg-success' }} text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="stat-number">₦{{ number_format($balance, 2) }}</div>
                        <div class="stat-label">{{ $balance > 0 ? 'Balance' : 'Paid' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Records -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="d-none d-md-table-cell">Class</th>
                        <th class="text-center">Total Fees</th>
                        <th class="text-center">Amount Paid</th>
                        <th class="text-center">Balance</th>
                        <th class="text-center">Paid</th>
                        <th class="text-center">To Pay</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="d-none d-md-table-cell">
                                <div class="d-md-none">{{ $payment->created_at->format('M d, Y') }}</div>
                                <small class="d-md-none d-block text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $payment->schoolClass->full_name ?? 'N/A' }}</td>
                            <td class="text-center">₦{{ number_format($payment->total_fees, 2) }}</td>
                            <td class="text-center">₦{{ number_format($payment->amount_paid, 2) }}</td>
                            <td class="text-center">₦{{ number_format($payment->balance, 2) }}</td>
                            <td class="text-center">{{ $payment->paid_percentage }}%</td>
                            <td class="text-center">{{ $payment->unpaid_percentage }}%</td>
                            <td class="text-center">
                                <span class="badge {{ $payment->status === 'paid' ? 'bg-success' : ($payment->status === 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-receipt fa-3x mb-3"></i>
                                <p>No payment records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($balance > 0)
        <div class="alert alert-warning mt-3">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Outstanding Balance</h6>
            <p class="mb-0">You have an outstanding balance of ₦{{ number_format($balance, 2) }}. Please visit the bursary office to make payment.</p>
        </div>
        @endif
    </div>
</div>
@endsection
