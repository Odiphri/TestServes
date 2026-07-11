@extends('owner.layout')

@php
    $platformName = \App\Models\SystemSetting::platformName();
    $platformLogo = \App\Models\SystemSetting::platformLogoUrl();
    $avatarUrl = $owner->profile_picture_url ?: asset('images/default-avatar.svg');
    $ownerMenu = [
        ['label' => 'Dashboard', 'route' => route('platform.dashboard'), 'active' => request()->routeIs('platform.dashboard')],
        ['label' => 'Profile', 'route' => route('platform.profile'), 'active' => request()->routeIs('platform.profile')],
        ['label' => 'School', 'route' => route('platform.school'), 'active' => request()->routeIs('platform.school')],
        ['label' => 'Branding', 'route' => route('platform.branding'), 'active' => request()->routeIs('platform.branding')],
        ['label' => 'Plans', 'route' => route('platform.plans'), 'active' => request()->routeIs('platform.plans')],
        ['label' => 'Payments', 'route' => route('platform.payments'), 'active' => request()->routeIs('platform.payments')],
    ];
@endphp

@section('body')
<div class="owner-app-shell">
    <button class="owner-mobile-menu" type="button" onclick="document.body.classList.toggle('owner-sidebar-open')">Menu</button>
    <aside class="owner-sidebar">
        <a class="owner-logo mb-4" href="{{ route('platform.dashboard') }}">
            @if($platformLogo)
                <img class="platform-logo-img" src="{{ $platformLogo }}" alt="{{ $platformName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';">
                <span class="owner-logo-mark platform-logo-fallback" style="display:none;">TS</span>
            @else
                <span class="owner-logo-mark">TS</span>
            @endif
            <span>{{ $platformName }}</span>
        </a>
        <div class="owner-side-profile">
            <img src="{{ $avatarUrl }}" alt="{{ $owner->name }}" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
            <strong>{{ $owner->name }}</strong>
            <span>{{ $school?->name ?? 'Owner workspace' }}</span>
        </div>
        <nav class="owner-side-nav">
            @foreach($ownerMenu as $item)
                <a class="{{ $item['active'] ? 'active' : '' }}" href="{{ $item['route'] }}" onclick="document.body.classList.remove('owner-sidebar-open')">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <form class="mt-auto" action="{{ route('platform.logout') }}" method="POST">
            @csrf
            <button class="btn btn-outline-light w-100" type="submit">Logout</button>
        </form>
    </aside>
    <main class="owner-app-main">
        <header class="owner-app-topbar">
            <div>
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Manage your school workspace.')</p>
            </div>
            <span class="status-pill">{{ ucfirst($school?->subscription_status ?? 'pending') }}</span>
        </header>
        @include('owner.partials.alerts')
        @if($school && in_array($school->status, ['deactivated', 'suspended', 'expired'], true))
            <div class="alert alert-warning">
                <strong>{{ $school->status === 'deactivated' ? 'School deactivated.' : 'Portal access limited.' }}</strong>
                {{ $school->deactivation_reason ?: 'Please contact TestServes support or resolve your subscription to restore full access.' }}
                @if($school->delete_scheduled_at)
                    This school is scheduled for deletion after {{ $school->delete_scheduled_at->format('M j, Y') }} unless it is reactivated.
                @endif
            </div>
        @endif
        @yield('content')
    </main>
</div>
@endsection
