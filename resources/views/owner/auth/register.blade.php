@extends('owner.layout')

@section('title', 'Signup')

@php
    $layoutPlatformName = \App\Models\SystemSetting::platformName();
    $layoutPlatformLogo = \App\Models\SystemSetting::platformLogoUrl();
    $selectedPlanId = old('subscription_plan_id');
@endphp

@section('body')
<main class="auth-screen register-screen">
    <section class="auth-panel">
        <a class="owner-logo auth-logo" href="{{ route('platform.home') }}">
            @if($layoutPlatformLogo)
                <img class="platform-logo-img" src="{{ $layoutPlatformLogo }}" alt="{{ $layoutPlatformName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';">
                <span class="owner-logo-mark platform-logo-fallback" style="display:none;">TS</span>
            @else
                <span class="owner-logo-mark">TS</span>
            @endif
            <span>{{ $layoutPlatformName }}</span>
        </a>

        <div class="auth-copy">
            <span class="owner-eyebrow">New workspace</span>
            <h1>Create your owner account.</h1>
            <p>This page is only for signup. The homepage can explain the product; here we just open your workspace.</p>
        </div>

        <div class="auth-mini-board">
            <div><strong>Required</strong><span>Name, email, password</span></div>
            <div><strong>Optional</strong><span>Plan and school details</span></div>
            <div><strong>Later</strong><span>Branding, payment, approval</span></div>
        </div>
    </section>

    <section class="auth-card-wrap">
        <div class="owner-card auth-card register-card">
            <div class="owner-form-meta align-items-start">
                <div>
                    <h2 class="owner-title">Start with the basics</h2>
                    <p class="owner-subtitle mb-0">Move step by step. Optional steps can be skipped.</p>
                </div>
                <button class="btn btn-outline-secondary btn-sm" form="ownerRegisterForm" type="submit" name="skip_optional" value="1">Skip this process</button>
            </div>

            @include('owner.partials.alerts')

            <form id="ownerRegisterForm" action="{{ route('platform.register.submit') }}" method="POST">
                @csrf

                <div class="wizard-steps" aria-label="Signup progress">
                    @foreach(['Account', 'Plan', 'School', 'Create'] as $step)
                        <button type="button" class="wizard-dot {{ $loop->first ? 'active' : '' }}" data-wizard-jump="{{ $loop->index }}">
                            <span>{{ $loop->iteration }}</span>{{ $step }}
                        </button>
                    @endforeach
                </div>

                <section class="wizard-panel active" data-wizard-panel data-title="Owner account">
                    <div class="owner-form-badge mb-3">Required</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="name">Full name</label><input id="name" class="form-control" name="name" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="Optional"></div>
                        <div class="col-md-6"><label class="form-label" for="password">Password</label><input id="password" class="form-control" type="password" name="password" required></div>
                        <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirm password</label><input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required></div>
                    </div>
                </section>

                <section class="wizard-panel" data-wizard-panel data-title="Plan">
                    <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                        <div><h3 class="h5 mb-1">Pick a plan, or leave it</h3><p class="text-muted mb-0">Choosing later will not show this signup flow again.</p></div>
                        <span class="owner-form-badge">Optional</span>
                    </div>
                    <div class="pricing-grid">
                        <label class="pricing-card {{ blank($selectedPlanId) ? 'selected' : '' }}">
                            <input type="radio" name="subscription_plan_id" value="" @checked(blank($selectedPlanId))>
                            <span class="pricing-top"><strong>Choose later</strong><em>No pressure</em></span>
                            <span class="pricing-price">NGN 0</span>
                            <span class="pricing-sub">Open the dashboard first, decide later.</span>
                        </label>
                        @foreach($plans as $plan)
                            @php
                                $monthly = (float) $plan->monthly_price;
                                $yearly = (float) $plan->yearly_price;
                                $annualFull = $monthly * 12;
                                $discount = $annualFull > 0 && $yearly > 0 && $yearly < $annualFull ? round((($annualFull - $yearly) / $annualFull) * 100) : 0;
                            @endphp
                            <label class="pricing-card {{ (string) $selectedPlanId === (string) $plan->id ? 'selected' : '' }}">
                                <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" @checked((string) $selectedPlanId === (string) $plan->id)>
                                <span class="pricing-top"><strong>{{ $plan->name }}</strong>@if($plan->is_recommended)<em>Recommended</em>@elseif($discount > 0)<em>{{ $discount }}% yearly off</em>@endif</span>
                                <span class="pricing-price">NGN {{ number_format($monthly, 0) }}<small>/month</small></span>
                                <span class="pricing-sub">NGN {{ number_format($yearly, 0) }} yearly @if($discount > 0) · save {{ $discount }}%@endif</span>
                                <span class="pricing-trial">{{ $plan->trial_days }} trial days</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="wizard-panel" data-wizard-panel data-title="School">
                    <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                        <div><h3 class="h5 mb-1">School details</h3><p class="text-muted mb-0">Fill what you know. Anything empty can be edited later.</p></div>
                        <span class="owner-form-badge">Optional</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="school_name">School name</label><input id="school_name" class="form-control" name="school_name" value="{{ old('school_name') }}"></div>
                        <div class="col-md-6"><label class="form-label" for="school_slug">Portal name</label><input id="school_slug" class="form-control" name="school_slug" value="{{ old('school_slug') }}" placeholder="greenfield"></div>
                        <div class="col-md-6"><label class="form-label" for="school_type">School type</label><select id="school_type" class="form-select" name="school_type"><option value="">Choose later</option>@foreach(['Nursery', 'Primary', 'Secondary', 'Combined'] as $type)<option value="{{ $type }}" @selected(old('school_type') === $type)>{{ $type }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label" for="expected_students_count">Expected students</label><input id="expected_students_count" class="form-control" type="number" min="1" name="expected_students_count" value="{{ old('expected_students_count') }}"></div>
                        <div class="col-12"><label class="form-label" for="school_address">Address</label><textarea id="school_address" class="form-control" name="school_address" rows="3">{{ old('school_address') }}</textarea></div>
                    </div>
                </section>

                <section class="wizard-panel" data-wizard-panel data-title="Create workspace">
                    <div class="review-box">
                        <strong>Ready.</strong>
                        <span>Your owner account will be created.</span>
                        <span>Optional setup will not be forced again on the dashboard.</span>
                        <span>You can edit school details, plan, branding, and profile from dashboard actions.</span>
                    </div>
                </section>

                <div class="wizard-actions">
                    <button class="btn btn-outline-secondary" type="button" data-wizard-back>Back</button>
                    <button class="btn btn-outline-secondary" type="button" data-wizard-skip>Skip step</button>
                    <button class="btn btn-primary px-4" type="button" data-wizard-next>Next</button>
                    <button class="btn btn-primary px-4 d-none" type="submit" data-wizard-submit>Create workspace</button>
                </div>

                <div class="owner-card-footer">Already registered? <a href="{{ route('platform.login') }}">Login</a></div>
            </form>
        </div>
    </section>
</main>

<script>
(() => {
    const panels = Array.from(document.querySelectorAll('[data-wizard-panel]'));
    const dots = Array.from(document.querySelectorAll('[data-wizard-jump]'));
    const back = document.querySelector('[data-wizard-back]');
    const skip = document.querySelector('[data-wizard-skip]');
    const next = document.querySelector('[data-wizard-next]');
    const submit = document.querySelector('[data-wizard-submit]');
    let index = 0;

    function show(nextIndex) {
        index = Math.max(0, Math.min(nextIndex, panels.length - 1));
        panels.forEach((panel, panelIndex) => panel.classList.toggle('active', panelIndex === index));
        dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === index));
        back.disabled = index === 0;
        next.classList.toggle('d-none', index === panels.length - 1);
        submit.classList.toggle('d-none', index !== panels.length - 1);
        skip.textContent = index === 0 ? 'Skip optional setup' : 'Skip step';
    }

    function accountIsValid() {
        if (index !== 0) return true;
        return Array.from(panels[0].querySelectorAll('[required]')).every((input) => input.reportValidity());
    }

    back.addEventListener('click', () => show(index - 1));
    next.addEventListener('click', () => accountIsValid() && show(index + 1));
    skip.addEventListener('click', () => accountIsValid() && show(index + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => {
        const target = Number(dot.dataset.wizardJump);
        if (target === 0 || accountIsValid()) show(target);
    }));
    document.querySelectorAll('.pricing-card input').forEach((input) => {
        input.addEventListener('change', () => {
            document.querySelectorAll('.pricing-card').forEach((card) => card.classList.remove('selected'));
            input.closest('.pricing-card')?.classList.add('selected');
        });
    });
    show(0);
})();
</script>
@endsection
