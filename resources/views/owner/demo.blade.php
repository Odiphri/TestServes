@extends('owner.app')

@section('title', 'Demo')
@section('page-title', 'Demo')
@section('page-subtitle', 'Request CBT demo access from the TestServes team.')

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <form class="dashboard-card" method="POST" action="{{ route('platform.demo.store') }}">
            @csrf
            <h2 class="h5">Request plan demo</h2>
            <p class="text-muted">Choose the plan you want to test before paying. A Sales Admin will prepare demo access for that plan.</p>
            <div class="mb-3">
                <label class="form-label">Plan to demo</label>
                <select class="form-select" name="subscription_plan_id" required>
                    <option value="">Choose plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id', $school?->subscription_plan_id) === (string) $plan->id)>{{ $plan->name }} - NGN {{ number_format($plan->monthly_price, 0) }}/month</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Preferred demo date</label>
                <input class="form-control" type="datetime-local" name="preferred_demo_date" value="{{ old('preferred_demo_date') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea class="form-control" rows="5" name="message" placeholder="Tell us what you want to test.">{{ old('message') }}</textarea>
            </div>
            <button class="btn btn-primary">Send request</button>
        </form>
    </div>

    <div class="col-lg-7">
        <section class="dashboard-card mb-3">
            <h2 class="h5">What each demo includes</h2>
            <div class="pricing-grid">
                @foreach($plans as $plan)
                    @php
                        $monthly = (float) $plan->monthly_price;
                        $yearly = (float) $plan->yearly_price;
                    @endphp
                    <article class="pricing-card">
                        <span class="pricing-top"><strong>{{ $plan->name }}</strong>@if($plan->is_recommended)<em>Recommended</em>@endif</span>
                        <span class="pricing-price">NGN {{ number_format($monthly, 0) }}<small>/month</small></span>
                        <span class="pricing-sub">NGN {{ number_format($yearly, 0) }} yearly</span>
                        <span class="pricing-trial">{{ $plan->trial_days }} trial days after activation</span>
                        @include('owner.partials.plan-inclusions', ['plan' => $plan])
                    </article>
                @endforeach
            </div>
        </section>

        <section class="dashboard-card">
            <h2 class="h5">Demo requests</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Plan</th><th>Status</th><th>Preferred date</th><th>Assigned</th><th>Demo link</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($demoRequests as $demoRequest)
                        <tr>
                            <td>{{ $demoRequest->plan?->name ?? 'No plan' }}</td>
                            <td><span class="status-pill">{{ ucfirst($demoRequest->status) }}</span></td>
                            <td>{{ optional($demoRequest->preferred_demo_date)->format('M j, Y g:ia') ?? 'Not set' }}</td>
                            <td>{{ $demoRequest->assignedAdmin?->name ?? 'Unassigned' }}</td>
                            <td>
                                @if($demoRequest->demo_url)
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ $demoRequest->demo_url }}" target="_blank" rel="noopener">Open demo</a>
                                @else
                                    <span class="text-muted small">Pending</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('platform.demo.destroy', $demoRequest) }}" onsubmit="return confirm('Delete this demo request?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No demo requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
