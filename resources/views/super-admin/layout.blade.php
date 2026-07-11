@php
    $platformAdmin = Auth::guard('platform_admin')->user();
    $platformName = \App\Models\SystemSetting::platformName();
    $platformLogo = \App\Models\SystemSetting::platformLogoUrl();
    $menu = [
        ['section' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-chart-line', 'route' => route('super-admin.dashboard'), 'active' => request()->routeIs('super-admin.dashboard')],
        ['section' => 'schools', 'label' => 'Schools', 'icon' => 'fa-school', 'route' => route('super-admin.schools.index'), 'active' => request()->routeIs('super-admin.schools*')],
        ['section' => 'school_owners', 'label' => 'School Owners', 'icon' => 'fa-user-tie', 'route' => route('super-admin.school-owners.index'), 'active' => request()->routeIs('super-admin.school-owners*')],
        ['section' => 'subscription_plans', 'label' => 'Subscription Plans', 'icon' => 'fa-layer-group', 'route' => route('super-admin.subscription-plans.index'), 'active' => request()->routeIs('super-admin.subscription-plans*') || request()->routeIs('super-admin.plans*')],
        ['section' => 'payments', 'label' => 'Payments', 'icon' => 'fa-credit-card', 'route' => route('super-admin.payments.index'), 'active' => request()->routeIs('super-admin.payments*')],
        ['section' => 'payment_disputes', 'label' => 'Payment Disputes', 'icon' => 'fa-scale-balanced', 'route' => route('super-admin.payment-disputes.index'), 'active' => request()->routeIs('super-admin.payment-disputes*')],
        ['section' => 'demo_requests', 'label' => 'Demo Requests', 'icon' => 'fa-handshake', 'route' => route('super-admin.demo-requests.index'), 'active' => request()->routeIs('super-admin.demo-requests*')],
        ['section' => 'support_tickets', 'label' => 'Support Tickets', 'icon' => 'fa-life-ring', 'route' => route('super-admin.support-tickets.index'), 'active' => request()->routeIs('super-admin.support-tickets*')],
        ['section' => 'live_support', 'label' => 'Live Support', 'icon' => 'fa-comments', 'route' => route('super-admin.live-support.index'), 'active' => request()->routeIs('super-admin.live-support*')],
        ['section' => 'activity_logs', 'label' => 'Activity Logs', 'icon' => 'fa-list-check', 'route' => route('super-admin.activity-logs.index'), 'active' => request()->routeIs('super-admin.activity-logs*')],
        ['section' => 'system_settings', 'label' => 'System Settings', 'icon' => 'fa-gear', 'route' => route('super-admin.system-settings.index'), 'active' => request()->routeIs('super-admin.system-settings*')],
        ['section' => 'admin_users', 'label' => 'Admin Users', 'icon' => 'fa-users-gear', 'route' => route('super-admin.admin-users.index'), 'active' => request()->routeIs('super-admin.admin-users*')],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - {{ $platformName }} Platform</title>
    @if($platformLogo)
        <link rel="icon" href="{{ $platformLogo }}">
        <link rel="apple-touch-icon" href="{{ $platformLogo }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --platform-primary: #0f172a;
            --platform-primary-soft: #1e293b;
            --platform-accent: #2563eb;
            --platform-success: #16a34a;
            --platform-warning: #d97706;
            --platform-danger: #dc2626;
            --platform-bg: #f8fafc;
            --platform-card: #ffffff;
            --platform-border: #e2e8f0;
            --platform-muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--platform-bg);
            color: #0f172a;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .platform-shell { min-height: 100vh; display: flex; }
        .platform-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 270px;
            background: linear-gradient(180deg, var(--platform-primary), var(--platform-primary-soft));
            color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 20;
        }
        .platform-brand { padding: 24px; border-bottom: 1px solid rgba(255,255,255,.12); }
        .platform-brand strong { display: block; font-size: 20px; }
        .platform-brand span { color: #cbd5e1; font-size: 13px; }
        .platform-brand-logo,
        .platform-brand-fallback {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 8px;
        }
        .platform-brand-logo {
            object-fit: contain;
            padding: 4px;
        }
        .platform-brand-fallback {
            display: grid;
            place-items: center;
            color: var(--platform-primary);
            font-weight: 800;
        }
        .platform-nav { padding: 14px; overflow-y: auto; flex: 1; }
        .platform-nav a {
            display: flex;
            gap: 10px;
            align-items: center;
            color: #dbeafe;
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .platform-nav a:hover, .platform-nav a.active { background: rgba(255,255,255,.12); color: #fff; }
        .platform-sidebar-footer { padding: 16px; border-top: 1px solid rgba(255,255,255,.12); }
        .platform-admin-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .platform-admin-chip:hover { color: #fff; }
        .platform-admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.22);
        }
        .platform-main { margin-left: 270px; width: calc(100% - 270px); padding: 24px; }
        .platform-topbar {
            background: var(--platform-card);
            border: 1px solid var(--platform-border);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }
        .platform-title { margin: 0; font-weight: 800; font-size: 24px; }
        .platform-subtitle { margin: 4px 0 0; color: var(--platform-muted); font-size: 14px; }
        .platform-card {
            background: var(--platform-card);
            border: 1px solid var(--platform-border);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
        .kpi { padding: 18px; }
        .kpi span { color: var(--platform-muted); font-size: 13px; }
        .kpi strong { display: block; font-size: 28px; margin-top: 6px; }
        .status-badge { border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 700; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-suspended, .status-expired, .status-inactive { background: #fee2e2; color: #991b1b; }
        .status-disabled, .status-rejected, .status-failed, .status-urgent { background: #fee2e2; color: #991b1b; }
        .status-trial { background: #dbeafe; color: #1d4ed8; }
        .status-paid, .status-completed, .status-resolved, .status-closed { background: #dcfce7; color: #166534; }
        .status-new, .status-open, .status-medium, .status-answered { background: #dbeafe; color: #1d4ed8; }
        .status-contacted, .status-scheduled, .status-in_progress, .status-investigating, .status-waiting, .status-high { background: #fef3c7; color: #92400e; }
        .actions-row { display: flex; gap: 6px; flex-wrap: wrap; }
        .color-dot { width: 22px; height: 22px; border-radius: 50%; border: 1px solid var(--platform-border); display: inline-block; }
        .mobile-toggle { display: none; }
        .platform-sidebar-scrim {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 15;
            background: rgba(15, 23, 42, .45);
        }
        .platform-sidebar-close {
            display: none;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 8px;
            background: rgba(255,255,255,.1);
            color: #fff;
            font-weight: 800;
            padding: 9px 12px;
            margin: 14px 14px 0;
        }
        @media (max-width: 992px) {
            .platform-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            body.sidebar-open .platform-sidebar { transform: translateX(0); }
            body.sidebar-open .platform-sidebar-scrim { display: block; }
            body.sidebar-open { overflow: hidden; }
            .platform-main { margin-left: 0; width: 100%; padding: 16px; }
            .mobile-toggle { display: inline-flex; }
            .platform-sidebar-close { display: inline-flex; justify-content: center; }
            .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 576px) {
            .platform-topbar { align-items: flex-start; flex-direction: column; }
            .kpi-grid { grid-template-columns: 1fr; }
            .platform-sidebar { width: min(86vw, 300px); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="platform-shell">
        <button class="platform-sidebar-scrim" type="button" onclick="closePlatformSidebar()" aria-label="Close navigation"></button>
        <aside class="platform-sidebar" id="platformSidebar">
            <button class="platform-sidebar-close" type="button" onclick="closePlatformSidebar()" aria-label="Close navigation">Close menu</button>
            <div class="platform-brand">
                @if($platformLogo)
                    <img class="platform-brand-logo" src="{{ $platformLogo }}" alt="{{ $platformName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';">
                    <span class="platform-brand-fallback" style="display:none;">TS</span>
                @else
                    <span class="platform-brand-fallback">TS</span>
                @endif
                <strong>{{ $platformName }}</strong>
                <span>{{ $platformAdmin?->roleLabel() ?? 'Platform Admin' }}</span>
            </div>
            <nav class="platform-nav">
                @foreach($menu as $item)
                    @continue($platformAdmin && ! $platformAdmin->canAccessPlatformSection($item['section']))
                    <a href="{{ $item['route'] }}" class="{{ $item['active'] ? 'active' : '' }}" onclick="closePlatformSidebar()">
                        <i class="fas {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="platform-sidebar-footer">
                <a class="platform-admin-chip" href="{{ route('super-admin.profile.edit') }}">
                    <img class="platform-admin-avatar" src="{{ $platformAdmin?->profile_picture_url ?? asset('images/default-avatar.svg') }}" alt="{{ $platformAdmin?->name }}" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                    <span>
                        <strong class="d-block small">{{ $platformAdmin?->name }}</strong>
                        <small class="text-white-50">Edit profile</small>
                    </span>
                </a>
                <form action="{{ route('super-admin.logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-light btn-sm w-100" type="submit">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </aside>
        <main class="platform-main">
            <div class="platform-topbar">
                <div class="d-flex gap-3 align-items-start">
                    <button class="btn btn-outline-secondary mobile-toggle" type="button" onclick="togglePlatformSidebar()" aria-label="Open navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="platform-title">@yield('title', 'Super Admin')</h1>
                        <p class="platform-subtitle">@yield('subtitle', 'Manage the TestServes SaaS platform.')</p>
                    </div>
                </div>
                <div class="text-end small text-muted">
                    <div>{{ ucwords(str_replace('_', ' ', $platformAdmin?->role ?? 'platform admin')) }}</div>
                    <div>{{ now()->format('M j, Y') }}</div>
                </div>
            </div>

            @include('super-admin.partials.alerts')
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function closePlatformSidebar() {
            document.body.classList.remove('sidebar-open');
        }

        function togglePlatformSidebar() {
            document.body.classList.toggle('sidebar-open');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePlatformSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                closePlatformSidebar();
            }
        });
    </script>
</body>
</html>
