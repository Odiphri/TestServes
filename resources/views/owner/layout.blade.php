<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $layoutPlatformName = \App\Models\SystemSetting::platformName();
        $layoutPlatformLogo = \App\Models\SystemSetting::platformLogoUrl();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'School Owner') - {{ $layoutPlatformName }}</title>
    @if($layoutPlatformLogo)
        <link rel="icon" href="{{ $layoutPlatformLogo }}">
        <link rel="apple-touch-icon" href="{{ $layoutPlatformLogo }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #102033;
            --owner-accent: #0f766e;
            --owner-accent-strong: #0b5f59;
            --owner-gold: #f59e0b;
            --owner-muted: #64748b;
            --owner-border: #dbe3ef;
            --owner-bg: #f5f8fb;
            --owner-panel: #ffffff;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background:
                linear-gradient(180deg, rgba(15, 118, 110, .08), rgba(255, 255, 255, 0) 340px),
                var(--owner-bg);
            color: var(--owner-primary);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        a { color: var(--owner-accent); font-weight: 700; text-decoration: none; }
        a:hover { color: var(--owner-accent-strong); }
        .owner-public {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-screen {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, .82fr) minmax(0, 1.18fr);
            background:
                radial-gradient(circle at 16% 12%, rgba(15, 118, 110, .18), transparent 280px),
                linear-gradient(135deg, #eef7f6 0%, #f8fafc 48%, #ffffff 100%);
        }
        .auth-panel {
            padding: clamp(24px, 5vw, 58px);
            border-right: 1px solid var(--owner-border);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
        }
        .auth-logo { width: fit-content; }
        .auth-copy h1 {
            font-size: clamp(36px, 5vw, 58px);
            line-height: 1;
            font-weight: 800;
            margin: 18px 0;
        }
        .auth-copy p {
            color: var(--owner-muted);
            font-size: 16px;
            line-height: 1.7;
            max-width: 520px;
            margin: 0;
        }
        .auth-mini-board {
            display: grid;
            gap: 10px;
        }
        .auth-mini-board div {
            background: rgba(255, 255, 255, .76);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 14px;
        }
        .auth-mini-board strong,
        .auth-mini-board span {
            display: block;
        }
        .auth-mini-board span {
            color: var(--owner-muted);
            font-size: 13px;
            margin-top: 4px;
        }
        .auth-card-wrap {
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .auth-card {
            background: #fff;
            box-shadow: 0 24px 74px rgba(16, 32, 51, .13);
        }
        .register-card { max-width: 860px; }
        .owner-app-shell {
            min-height: 100vh;
            display: flex;
            background: #f6f9fc;
        }
        .owner-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 270px;
            background: linear-gradient(180deg, #102033, #16324e);
            color: #fff;
            padding: 22px 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            z-index: 40;
        }
        .owner-mobile-menu {
            display: none;
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 50;
            border: 0;
            border-radius: 999px;
            background: var(--owner-accent);
            color: #fff;
            font-weight: 800;
            padding: 12px 16px;
            box-shadow: 0 16px 34px rgba(15, 118, 110, .28);
        }
        .owner-sidebar-scrim {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 35;
            background: rgba(15, 23, 42, .45);
        }
        .owner-sidebar-close {
            display: none;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 8px;
            background: rgba(255,255,255,.1);
            color: #fff;
            font-weight: 800;
            padding: 9px 12px;
            width: 100%;
        }
        .owner-sidebar .owner-logo { color: #fff; }
        .owner-side-profile {
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.08);
            border-radius: 8px;
            padding: 14px;
            display: grid;
            gap: 7px;
        }
        .owner-side-profile img {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.28);
            background: rgba(255,255,255,.16);
        }
        .owner-side-profile strong,
        .owner-side-profile span {
            display: block;
        }
        .owner-side-profile span {
            color: #cbd5e1;
            font-size: 13px;
        }
        .owner-side-nav {
            display: grid;
            gap: 6px;
        }
        .owner-side-nav a {
            color: #dbeafe;
            border-radius: 8px;
            padding: 11px 12px;
            text-decoration: none;
            font-weight: 800;
        }
        .owner-side-nav a:hover,
        .owner-side-nav a.active {
            color: #fff;
            background: rgba(255,255,255,.13);
        }
        .owner-app-main {
            width: calc(100% - 270px);
            margin-left: 270px;
            padding: 24px;
        }
        .owner-app-topbar {
            background: #fff;
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 18px;
            box-shadow: 0 10px 30px rgba(15,23,42,.05);
        }
        .owner-app-topbar h1 {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
        }
        .owner-app-topbar p {
            margin: 4px 0 0;
            color: var(--owner-muted);
        }
        .owner-nav {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 18px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .owner-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 18px;
            color: var(--owner-primary);
        }
        .owner-logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--owner-primary);
            color: #fff;
            box-shadow: 0 10px 26px rgba(16, 32, 51, .18);
        }
        .platform-logo-img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
            flex: 0 0 auto;
        }
        .owner-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .owner-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 48px;
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(360px, .92fr);
            gap: 28px;
            align-items: center;
        }
        .owner-brand-panel {
            color: var(--owner-primary);
            padding: 16px 0;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .owner-eyebrow {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 8px;
            background: #e6fffb;
            color: #115e59;
            border: 1px solid #b7ece6;
            border-radius: 999px;
            padding: 7px 11px;
            font-size: 13px;
            font-weight: 800;
        }
        .owner-brand-panel h1 {
            font-size: clamp(42px, 7vw, 72px);
            line-height: .98;
            font-weight: 800;
            margin: 0;
            max-width: 760px;
        }
        .owner-brand-panel p {
            color: #4b5f78;
            font-size: 18px;
            line-height: 1.7;
            max-width: 660px;
            margin: 0;
        }
        .owner-points {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .owner-point {
            background: rgba(255,255,255,.78);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 14px;
            box-shadow: 0 10px 28px rgba(16, 32, 51, .06);
            color: #31445a;
            font-size: 14px;
        }
        .owner-point strong {
            display: block;
            color: var(--owner-primary);
            font-size: 18px;
            margin-bottom: 3px;
        }
        .owner-preview {
            background: #102033;
            color: #d9e8f7;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 26px 70px rgba(16, 32, 51, .22);
            max-width: 620px;
            border: 1px solid rgba(255,255,255,.08);
        }
        .preview-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,.09);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .preview-status {
            background: rgba(15, 118, 110, .22);
            color: #99f6e4;
            border: 1px solid rgba(153, 246, 228, .24);
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 800;
        }
        .preview-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 12px;
        }
        .preview-card {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 8px;
            padding: 13px;
        }
        .preview-card span {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .preview-card strong {
            display: block;
            color: #fff;
            margin-top: 6px;
            font-size: 21px;
        }
        .preview-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid rgba(255,255,255,.08);
            font-size: 14px;
        }
        .preview-row:last-child { border-bottom: 0; }
        .owner-content { display: grid; place-items: center; }
        .owner-card {
            width: min(100%, 760px);
            background: rgba(255,255,255,.92);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(16, 32, 51, .12);
            padding: 30px;
            backdrop-filter: blur(12px);
        }
        .owner-card.narrow { max-width: 440px; }
        .owner-title { font-weight: 800; margin-bottom: 6px; }
        .owner-subtitle { color: var(--owner-muted); margin-bottom: 24px; }
        .btn-primary {
            background: var(--owner-accent);
            border-color: var(--owner-accent);
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(15, 118, 110, .22);
        }
        .btn-primary:hover { background: var(--owner-accent-strong); border-color: var(--owner-accent-strong); }
        .btn-outline-secondary {
            border-color: var(--owner-border);
            color: var(--owner-primary);
            font-weight: 700;
            background: rgba(255,255,255,.75);
        }
        .form-control, .form-select {
            border-color: var(--owner-border);
            border-radius: 8px;
            padding: 11px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--owner-accent);
            box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .14);
        }
        .form-label { font-weight: 600; font-size: 14px; }
        .owner-form-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 20px;
        }
        .owner-form-badge {
            color: #115e59;
            background: #e6fffb;
            border: 1px solid #b7ece6;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
        }
        .owner-dashboard { max-width: 1180px; margin: 0 auto; padding: 24px; }
        .owner-dashboard-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }
        .dashboard-card { background: #fff; border: 1px solid var(--owner-border); border-radius: 8px; padding: 22px; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .cockpit-dashboard { max-width: 1220px; }
        .cockpit-hero {
            background: #102033;
            color: #fff;
            border-radius: 8px;
            padding: 26px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
            box-shadow: 0 24px 60px rgba(16, 32, 51, .18);
        }
        .cockpit-hero h2 {
            font-size: clamp(30px, 4vw, 48px);
            font-weight: 800;
            margin: 14px 0 8px;
        }
        .cockpit-hero p {
            color: #cbd5e1;
            max-width: 740px;
            margin: 0;
        }
        .cockpit-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .cockpit-badge-card {
            min-width: 150px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            padding: 16px;
            display: grid;
            justify-items: center;
            gap: 8px;
            text-align: center;
        }
        .cockpit-badge-card span { color: #cbd5e1; font-size: 13px; }
        .cockpit-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .card-kicker {
            display: block;
            color: var(--owner-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .dashboard-card h3 {
            font-size: 20px;
            font-weight: 800;
        }
        .dashboard-card p { color: var(--owner-muted); }
        .school-logo-preview.large {
            width: 88px;
            height: 88px;
            background: rgba(255,255,255,.96);
        }
        .summary-list {
            display: grid;
            gap: 12px;
        }
        .summary-list div {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--owner-border);
            padding-bottom: 10px;
        }
        .summary-list div:last-child { border-bottom: 0; padding-bottom: 0; }
        .summary-list span { color: var(--owner-muted); }
        .summary-list strong { text-align: right; }
        .status-pill { border-radius: 999px; padding: 6px 11px; background: #fef3c7; color: #92400e; font-weight: 700; font-size: 12px; }
        .owner-avatar {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
            border: 2px solid #fff;
            box-shadow: 0 10px 24px rgba(16, 32, 51, .14);
            flex: 0 0 auto;
        }
        .owner-avatar.small { width: 48px; height: 48px; }
        .owner-progress {
            height: 10px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .owner-progress span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, var(--owner-accent), var(--owner-gold));
            border-radius: inherit;
        }
        .setup-checks {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .setup-checks span {
            display: inline-flex;
            border: 1px solid var(--owner-border);
            background: #f8fafc;
            color: var(--owner-muted);
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 800;
        }
        .setup-checks span.done {
            color: #166534;
            background: #dcfce7;
            border-color: #bbf7d0;
        }
        .owner-card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid var(--owner-border);
        }
        .school-logo-preview {
            width: 66px;
            height: 66px;
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #f8fafc;
            padding: 6px;
            flex: 0 0 auto;
        }
        .school-logo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .plan-market {
            display: grid;
            gap: 10px;
        }
        .plan-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            background: #fff;
        }
        .plan-option.selected {
            border-color: var(--owner-accent);
            background: #f0fdfa;
        }
        .plan-option strong,
        .plan-option small {
            display: block;
        }
        .plan-option small {
            color: var(--owner-muted);
            margin-top: 3px;
        }
        .plan-option em {
            display: inline-flex;
            margin-left: 6px;
            border-radius: 999px;
            padding: 3px 7px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-style: normal;
            font-weight: 800;
        }
        .owner-card-footer {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid var(--owner-border);
            color: var(--owner-muted);
            font-size: 14px;
        }
        .wizard-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 20px;
        }
        .wizard-dot {
            border: 1px solid var(--owner-border);
            background: #fff;
            color: var(--owner-muted);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 13px;
            text-align: left;
        }
        .wizard-dot span {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e2e8f0;
            color: var(--owner-primary);
            flex: 0 0 auto;
        }
        .wizard-dot.active {
            border-color: var(--owner-accent);
            background: #f0fdfa;
            color: var(--owner-primary);
        }
        .wizard-dot.active span {
            background: var(--owner-accent);
            color: #fff;
        }
        .wizard-panel { display: none; }
        .wizard-panel.active { display: block; }
        .wizard-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--owner-border);
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .pricing-card {
            min-height: 180px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            background: #fff;
            padding: 16px;
            cursor: pointer;
            box-shadow: 0 10px 26px rgba(16, 32, 51, .05);
            transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease;
        }
        .pricing-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(16, 32, 51, .08);
        }
        .pricing-card.selected {
            border-color: var(--owner-accent);
            background: #f0fdfa;
        }
        .pricing-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .pricing-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }
        .pricing-top strong {
            color: var(--owner-primary);
            font-size: 17px;
            line-height: 1.25;
        }
        .pricing-top em {
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            padding: 4px 8px;
            font-size: 11px;
            font-style: normal;
            font-weight: 800;
            white-space: nowrap;
        }
        .pricing-price {
            font-size: 28px;
            font-weight: 800;
            color: var(--owner-primary);
            line-height: 1;
        }
        .pricing-price small {
            color: var(--owner-muted);
            font-size: 13px;
            font-weight: 700;
        }
        .pricing-sub,
        .pricing-trial,
        .pricing-features {
            color: var(--owner-muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .rich-pricing {
            align-items: stretch;
        }
        .rich-pricing .pricing-card {
            min-height: 360px;
        }
        .feature-title {
            color: var(--owner-primary);
            font-size: 13px;
            font-weight: 800;
            margin-top: 4px;
        }
        .pricing-feature-list {
            margin: 0;
            padding-left: 18px;
            color: #334155;
            font-size: 13px;
            line-height: 1.55;
        }
        .feature-details {
            border-top: 1px solid var(--owner-border);
            padding-top: 8px;
        }
        .feature-details summary {
            cursor: pointer;
            color: var(--owner-accent);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .payment-summary,
        .bank-box {
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            background: #f8fafc;
            padding: 14px;
            margin-bottom: 16px;
        }
        .payment-summary span,
        .payment-summary strong,
        .payment-summary small {
            display: block;
        }
        .payment-summary span,
        .payment-summary small {
            color: var(--owner-muted);
        }
        .bank-box {
            display: grid;
            gap: 8px;
        }
        .bank-box div {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid var(--owner-border);
            padding-top: 8px;
        }
        .bank-box span { color: var(--owner-muted); }
        .bank-box p {
            margin: 4px 0 0;
            color: var(--owner-muted);
            line-height: 1.6;
        }
        .pricing-trial {
            width: fit-content;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            padding: 5px 8px;
            font-weight: 800;
        }
        .review-box {
            display: grid;
            gap: 10px;
            border: 1px solid var(--owner-border);
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
        }
        .review-box span {
            color: var(--owner-muted);
        }
        .landing-hero {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 56px 0 34px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, .92fr);
            gap: 34px;
            align-items: center;
        }
        .landing-copy h1 {
            font-size: clamp(44px, 7vw, 76px);
            line-height: .96;
            letter-spacing: 0;
            font-weight: 800;
            margin: 18px 0;
            max-width: 790px;
        }
        .landing-copy p {
            color: #4b5f78;
            font-size: 18px;
            line-height: 1.75;
            max-width: 690px;
            margin: 0;
        }
        .landing-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }
        .landing-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 30px;
            max-width: 620px;
        }
        .landing-metrics div {
            background: rgba(255,255,255,.82);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 14px;
            box-shadow: 0 10px 26px rgba(16, 32, 51, .06);
        }
        .landing-metrics strong {
            display: block;
            font-size: 26px;
            line-height: 1;
        }
        .landing-metrics span {
            display: block;
            color: var(--owner-muted);
            font-size: 13px;
            margin-top: 5px;
            font-weight: 700;
        }
        .landing-product {
            min-width: 0;
        }
        .product-window {
            background: #102033;
            color: #d9e8f7;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 8px;
            box-shadow: 0 30px 80px rgba(16, 32, 51, .22);
            overflow: hidden;
        }
        .product-bar {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .product-bar span {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #94a3b8;
        }
        .product-bar span:first-child { background: #f87171; }
        .product-bar span:nth-child(2) { background: #fbbf24; }
        .product-bar span:nth-child(3) { background: #34d399; }
        .product-bar strong {
            margin-left: auto;
            color: #fff;
            font-size: 13px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(160px, .85fr);
            gap: 14px;
            padding: 16px;
        }
        .product-main,
        .product-side > div {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 8px;
        }
        .product-main {
            padding: 16px;
        }
        .product-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
        }
        .product-header span,
        .product-side span {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .product-header h2 {
            color: #fff;
            font-size: 28px;
            margin: 5px 0 0;
        }
        .product-header em {
            background: rgba(245, 158, 11, .17);
            color: #fde68a;
            border: 1px solid rgba(253, 230, 138, .22);
            border-radius: 999px;
            padding: 6px 9px;
            font-style: normal;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .product-progress {
            height: 10px;
            background: rgba(255,255,255,.09);
            border-radius: 999px;
            overflow: hidden;
            margin: 20px 0;
        }
        .product-progress div {
            height: 100%;
            background: linear-gradient(90deg, #14b8a6, #f59e0b);
            border-radius: inherit;
        }
        .product-steps {
            display: grid;
            gap: 9px;
        }
        .product-steps div {
            color: #cbd5e1;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 8px;
            padding: 10px;
            font-size: 14px;
        }
        .product-steps .done { color: #99f6e4; }
        .product-steps .active { color: #fff; border-color: rgba(20, 184, 166, .42); }
        .product-side {
            display: grid;
            gap: 12px;
        }
        .product-side > div {
            padding: 13px;
        }
        .product-side strong {
            display: block;
            color: #fff;
            margin-top: 8px;
            font-size: 18px;
        }
        .landing-section {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 64px;
        }
        .section-heading {
            max-width: 720px;
            margin-bottom: 18px;
        }
        .section-heading h2 {
            font-weight: 800;
            font-size: clamp(30px, 4vw, 44px);
            margin: 14px 0 0;
        }
        .landing-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .landing-cards article {
            background: rgba(255,255,255,.86);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 14px 34px rgba(16, 32, 51, .07);
        }
        .landing-cards article > strong {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            background: #e6fffb;
            color: #115e59;
            border-radius: 8px;
            margin-bottom: 14px;
        }
        .landing-cards h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .landing-cards p {
            color: var(--owner-muted);
            margin: 0;
            line-height: 1.6;
        }
        @media (max-width: 900px) {
            .auth-screen { grid-template-columns: 1fr; }
            .auth-panel { border-right: 0; border-bottom: 1px solid var(--owner-border); }
            .owner-sidebar {
                position: fixed;
                width: min(82vw, 300px);
                transform: translateX(-105%);
                transition: transform .2s ease;
                overflow-y: auto;
            }
            body.owner-sidebar-open .owner-sidebar { transform: translateX(0); }
            body.owner-sidebar-open .owner-sidebar-scrim { display: block; }
            body.owner-sidebar-open { overflow: hidden; }
            .owner-mobile-menu { display: inline-flex; }
            .owner-sidebar-close { display: inline-flex; justify-content: center; }
            .owner-app-shell {
                display: block;
            }
            .owner-app-main {
                width: 100%;
                margin-left: 0;
                padding: 16px;
            }
            .owner-app-topbar {
                align-items: flex-start;
                flex-direction: column;
            }
            .owner-shell, .landing-hero { grid-template-columns: 1fr; }
            .owner-brand-panel { padding: 8px 0; }
            .owner-points, .preview-grid, .landing-metrics, .landing-cards, .product-grid, .pricing-grid, .cockpit-grid, .cockpit-hero { grid-template-columns: 1fr; }
            .owner-nav { align-items: flex-start; }
            .owner-brand-panel h1 { font-size: 42px; }
        }
        @media (max-width: 576px) {
            .owner-nav { flex-direction: column; }
            .owner-nav-actions { width: 100%; justify-content: flex-start; }
            .owner-card { padding: 22px; }
            .owner-dashboard-head { align-items: flex-start; flex-direction: column; }
            .wizard-steps { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    @yield('body')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.pricing-card input').forEach((input) => {
            input.addEventListener('change', () => {
                const grid = input.closest('.pricing-grid');
                grid?.querySelectorAll('.pricing-card').forEach((card) => card.classList.remove('selected'));
                input.closest('.pricing-card')?.classList.add('selected');
            });
        });

        function closeOwnerSidebar() {
            document.body.classList.remove('owner-sidebar-open');
        }

        function toggleOwnerSidebar() {
            document.body.classList.toggle('owner-sidebar-open');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeOwnerSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) {
                closeOwnerSidebar();
            }
        });
    </script>
</body>
</html>
