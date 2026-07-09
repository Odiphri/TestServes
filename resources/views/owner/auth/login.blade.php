@extends('owner.layout')

@section('title', 'Login')

@php
    $layoutPlatformName = \App\Models\SystemSetting::platformName();
    $layoutPlatformLogo = \App\Models\SystemSetting::platformLogoUrl();
@endphp

@section('body')
<main class="auth-screen">
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
            <span class="owner-eyebrow">Account access</span>
            <h1>Sign in to your workspace.</h1>
            <p>Owners and platform admins use this same login. We will send you to the right dashboard after sign in.</p>
        </div>

        <div class="auth-mini-board">
            <div><strong>Owner</strong><span>School setup, plan, branding</span></div>
            <div><strong>Admin</strong><span>Platform control center</span></div>
            <div><strong>CBT</strong><span>Only on school subdomains</span></div>
        </div>
    </section>

    <section class="auth-card-wrap">
        <div class="owner-card narrow auth-card">
            <div class="owner-form-meta">
                <div>
                    <h2 class="owner-title">Welcome back</h2>
                    <p class="owner-subtitle mb-0">Continue from where you stopped.</p>
                </div>
                <span class="owner-form-badge">Secure</span>
            </div>

            @include('owner.partials.alerts')

            <form action="{{ route('platform.login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" class="form-control" type="password" name="password" required>
                </div>
                <div class="form-check mb-4">
                    <input id="remember" class="form-check-input" type="checkbox" name="remember" value="1">
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">Sign in</button>
            </form>

            <div class="owner-card-footer text-center">
                New here? <a href="{{ route('platform.register') }}">Create owner account</a>
            </div>
        </div>
    </section>
</main>
@endsection
