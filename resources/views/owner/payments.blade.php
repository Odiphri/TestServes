@extends('owner.app')
@section('title', 'Payments')
@section('page-title', 'Payments')
@section('page-subtitle', 'Manual transfer now. Paystack checkout works when keys are enabled in settings.')
@section('content')
@php
    $selectedPlanId = old('subscription_plan_id', $school?->subscription_plan_id);
@endphp
<div class="row g-3">
    <div class="col-xl-5">
        <section class="dashboard-card mb-3">
            <h2 class="h5">Bank transfer details</h2>
            <div class="bank-box mb-0">
                <div><span>Bank</span><b>{{ $settings['bank_name'] ?? 'Not set yet' }}</b></div>
                <div><span>Account name</span><b>{{ $settings['account_name'] ?? 'Not set yet' }}</b></div>
                <div><span>Account number</span><b>{{ $settings['account_number'] ?? 'Not set yet' }}</b></div>
                @if(filled($settings['manual_payment_instructions'] ?? null))
                    <p>{{ $settings['manual_payment_instructions'] }}</p>
                @else
                    <p>Transfer to the account above, then submit the reference below for Finance Admin confirmation.</p>
                @endif
            </div>
        </section>

        @if($school?->subscription_plan_id && ! in_array($school?->status, ['active', 'trial'], true))
            <form class="dashboard-card mb-3" action="{{ route('platform.trial.start') }}" method="POST">
                @csrf
                <h2 class="h5">Start free trial</h2>
                <p class="text-muted mb-3">Open the school portal with the selected plan features before payment approval.</p>
                <button class="btn btn-primary">Start free trial</button>
            </form>
        @endif

        <form class="dashboard-card" action="{{ route('platform.payments.store') }}" method="POST">
            @csrf
            <h2 class="h5">Submit manual payment</h2>
            <p class="text-muted">Select a plan and billing cycle. Your portal opens only after Finance Admin marks the payment as paid.</p>

            <div class="mb-3">
                <label class="form-label">Plan</label>
                <select class="form-select" name="subscription_plan_id" required>
                    <option value="">Choose plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected((string) $selectedPlanId === (string) $plan->id)>{{ $plan->name }} - NGN {{ number_format($plan->monthly_price, 0) }}/month</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Billing cycle</label>
                <select class="form-select" name="billing_cycle" required>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment method</label>
                <select class="form-select" name="payment_method" required>
                    <option value="bank_transfer">Bank transfer</option>
                    <option value="cash">Cash</option>
                    <option value="manual">Manual/other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Reference</label>
                <input class="form-control" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="Bank transfer reference">
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="notes" rows="3" placeholder="Tell Finance what you paid and when.">{{ old('notes') }}</textarea>
            </div>
            <button class="btn btn-primary">Submit manual payment</button>
        </form>

        @if($paystackEnabled)
            <form class="dashboard-card mt-3" action="{{ route('platform.payments.paystack') }}" method="POST">
                @csrf
                <h2 class="h5">Pay with Paystack</h2>
                <div class="mb-3">
                    <label class="form-label">Plan</label>
                    <select class="form-select" name="subscription_plan_id" required>
                        <option value="">Choose plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) $selectedPlanId === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Billing cycle</label>
                    <select class="form-select" name="billing_cycle" required>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <button class="btn btn-primary">Open Paystack checkout</button>
            </form>
        @endif
    </div>

    <div class="col-xl-7">
        <section class="dashboard-card mb-3">
            <h2 class="h5">Plan details</h2>
            <div class="pricing-grid">
                @foreach($plans as $plan)
                    @php
                        $monthly = (float) $plan->monthly_price;
                        $yearly = (float) $plan->yearly_price;
                        $annualFull = $monthly * 12;
                        $discount = $annualFull > 0 && $yearly > 0 && $yearly < $annualFull ? round((($annualFull - $yearly) / $annualFull) * 100) : 0;
                        $features = $plan->features ?? [];
                    @endphp
                    <article class="pricing-card {{ (string) $selectedPlanId === (string) $plan->id ? 'selected' : '' }}">
                        <span class="pricing-top"><strong>{{ $plan->name }}</strong>@if($plan->is_recommended)<em>Recommended</em>@elseif($discount > 0)<em>{{ $discount }}% yearly off</em>@endif</span>
                        <span class="pricing-price">NGN {{ number_format($monthly, 0) }}<small>/month</small></span>
                        <span class="pricing-sub">NGN {{ number_format($yearly, 0) }} yearly @if($discount > 0) · save {{ $discount }}%@endif</span>
                        <span class="feature-title">Included features</span>
                        @if($features)
                            <ul class="pricing-feature-list">
                                @foreach(array_slice($features, 0, 6) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            @if(count($features) > 6)
                                <details class="feature-details">
                                    <summary>Show {{ count($features) - 6 }} more features</summary>
                                    <ul class="pricing-feature-list">
                                        @foreach(array_slice($features, 6) as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        @else
                            <span class="pricing-sub">No features listed for this plan yet.</span>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="dashboard-card">
            <h2 class="h5">Payment history</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Period</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($payments ?? [] as $payment)
                        <tr>
                            <td>{{ $payment->payment_reference }}</td>
                            <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            <td><span class="status-pill">{{ ucfirst($payment->status) }}</span></td>
                            <td>{{ optional($payment->period_start)->format('M j') ?? '-' }} - {{ optional($payment->period_end)->format('M j, Y') ?? '-' }}</td>
                            <td>
                                @if($payment->status !== 'paid')
                                    <form method="POST" action="{{ route('platform.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment submission?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-muted small">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No payments yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $payments?->links() }}
        </section>
    </div>
</div>
@endsection
