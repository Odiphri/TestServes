@extends('owner.app')
@section('title', 'Plans')
@section('page-title', 'Plans')
@section('page-subtitle', 'Choose what your school wants to buy.')
@section('content')
<form class="dashboard-card" action="{{ route('platform.plan.update') }}" method="POST">
    @csrf @method('PUT')
    <div class="pricing-grid rich-pricing">
        <label class="pricing-card {{ blank(old('subscription_plan_id', $school?->subscription_plan_id)) ? 'selected' : '' }}">
            <input type="radio" name="subscription_plan_id" value="" @checked(blank(old('subscription_plan_id', $school?->subscription_plan_id)))>
            <span class="pricing-top"><strong>Choose later</strong><em>No access yet</em></span>
            <span class="pricing-price">NGN 0</span>
            <span class="pricing-sub">No portal access until a plan is selected and payment is confirmed.</span>
        </label>
        @foreach($plans as $plan)
            @php
                $monthly=(float)$plan->monthly_price;
                $yearly=(float)$plan->yearly_price;
                $annualFull=$monthly*12;
                $discount=$annualFull>0&&$yearly>0&&$yearly<$annualFull?round((($annualFull-$yearly)/$annualFull)*100):0;
            @endphp
            <label class="pricing-card {{ (int) old('subscription_plan_id', $school?->subscription_plan_id) === $plan->id ? 'selected' : '' }}">
                <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" @checked((int) old('subscription_plan_id', $school?->subscription_plan_id) === $plan->id)>
                <span class="pricing-top">
                    <strong>{{ $plan->name }}</strong>
                    @if($plan->is_recommended)<em>Recommended</em>@elseif($discount > 0)<em>{{ $discount }}% yearly off</em>@endif
                </span>
                <span class="pricing-price">NGN {{ number_format($monthly, 0) }}<small>/month</small></span>
                <span class="pricing-sub">NGN {{ number_format($yearly, 0) }} yearly @if($discount > 0) · save {{ $discount }}%@endif</span>
                <span class="pricing-trial">{{ $plan->trial_days }} trial days</span>
                @include('owner.partials.plan-inclusions', ['plan' => $plan])
            </label>
        @endforeach
    </div>
    <div class="owner-card-actions">
        <button class="btn btn-primary">Save selected plan</button>
        <a class="btn btn-outline-secondary" href="{{ route('platform.payments') }}">Continue to payment</a>
    </div>
</form>
@endsection
