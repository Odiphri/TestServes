@extends('super-admin.layout')

@section('title', 'Subscription Plans')
@section('subtitle', 'Manage SaaS packages, pricing, trial length, and included app features.')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('super-admin.subscription-plans.create') }}" class="btn btn-primary">Create plan</a>
</div>
<div class="platform-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Pricing</th>
                    <th>Features</th>
                    <th>Trial</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td>
                            <strong>{{ $plan->name }}</strong>
                            @if($plan->is_recommended)<span class="badge bg-primary ms-1">Recommended</span>@endif
                            <div class="small text-muted">{{ $plan->slug }}</div>
                        </td>
                        <td>
                            <div>Monthly: NGN {{ number_format($plan->monthly_price, 2) }}</div>
                            <div class="small text-muted">Yearly: NGN {{ number_format($plan->yearly_price, 2) }}</div>
                        </td>
                        <td>
                            @if($plan->features)
                                <div>{{ count($plan->features) }} included</div>
                                <div class="small text-muted">{{ implode(', ', array_slice($plan->features, 0, 4)) }}</div>
                            @else
                                <span class="text-muted">No features selected</span>
                            @endif
                        </td>
                        <td>{{ $plan->trial_days }} days</td>
                        <td><span class="status-badge status-{{ $plan->status }}">{{ ucfirst($plan->status) }}</span></td>
                        <td>
                            <div class="actions-row">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.subscription-plans.edit', $plan) }}">Edit</a>
                                <form action="{{ route('super-admin.subscription-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Delete this plan or mark it inactive if schools use it?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No subscription plans yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $plans->links() }}
</div>
@endsection
