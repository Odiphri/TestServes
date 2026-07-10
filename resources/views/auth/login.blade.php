<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $portalSchool = $currentSchool ?? null;
        $schoolName = $portalSchool?->branding?->portal_display_name ?? $portalSchool?->name ?? $schoolSettings?->school_name ?? 'TestServes';
        $schoolIcon = $portalSchool?->branding?->logo_url ?? $schoolSettings?->logo_url ?? \App\Models\SystemSetting::platformLogoUrl();
    @endphp
    <title>{{ $schoolSettings?->school_name ?? 'TestServes' }} CBT Portal - Login</title>
    <link rel="icon" href="{{ $schoolIcon }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ $schoolIcon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0B1F5B;
            --primary-dark: #081645;
            --accent: #1E88FF;
            --accent-light: #4DA3FF;
            --background: #F8FAFC;
            --surface: #FFFFFF;
            --text: #111827;
            --text-secondary: #6B7280;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 50%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        /* Floating geometric shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--accent);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 300px;
            height: 300px;
            background: var(--primary);
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            background: var(--accent-light);
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
        
        .login-container {
            width: min(92vw, 600px);
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        .login-card {
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 28px 44px 24px;
            text-align: center;
            position: relative;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-light), var(--primary), var(--accent-light));
        }
        
        .school-logo {
            width: 76px;
            height: 76px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 15px;
            font-weight: 400;
        }
        
        .login-body {
            padding: 28px 44px 26px;
            background: var(--surface);
        }

        .login-body .mb-4 {
            margin-bottom: 1rem !important;
        }
        
        .form-label {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: var(--background);
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(30, 136, 255, 0.1);
            background: var(--surface);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 32px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(30, 136, 255, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 136, 255, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(30, 136, 255, 0.1);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            font-size: 14px;
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(30, 136, 255, 0.1) 0%, rgba(11, 31, 91, 0.1) 100%);
            color: var(--primary);
            border-left: 4px solid var(--accent);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            color: #DC2626;
            border-left: 4px solid #EF4444;
        }
        
        .link-text {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .link-text:hover {
            color: var(--primary);
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 0 15px;
            }
            
            .login-card {
                border-radius: 20px;
            }
            
            .login-header {
                padding: 28px 28px 24px;
            }
            
            .login-body {
                padding: 26px 28px 24px;
            }
            
            .shape {
                display: none;
            }
        }
    </style>
    <link href="{{ asset('css/testserves-premium.css') }}" rel="stylesheet">
</head>
<body class="testserves-login-shell">
    <!-- Floating geometric shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="school-logo">
                    <img src="{{ $schoolIcon }}" alt="{{ $schoolName }} logo" onerror="this.onerror=null; this.src='{{ asset('images/default-school-icon.svg') }}';">
                </div>
                <h1>{{ $schoolSettings?->school_name ?? 'TestServes' }}</h1>
                <p>{{ $schoolSettings?->motto ?: 'Computer Based Testing' }}</p>
            </div>
            
            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Oops!</strong> There were some problems with your input.<br><br>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="portal_id" class="form-label">Portal ID</label>
                        <input id="portal_id" type="text" 
                               class="form-control @error('portal_id') is-invalid @enderror" 
                               name="portal_id" 
                               value="{{ old('portal_id') }}" 
                               placeholder="Enter your Portal ID"
                               required 
                               autocomplete="username" 
                               autofocus>
                        @error('portal_id')
                            <div class="invalid-feedback" style="color: #EF4444; font-size: 13px; margin-top: 5px;">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               placeholder="Enter your password"
                               required 
                               autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback" style="color: #EF4444; font-size: 13px; margin-top: 5px;">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember" style="color: var(--text-secondary); font-size: 14px;">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-login">
                            Sign In
                        </button>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-center">
                            <a href="{{ route('password.request') }}" class="link-text">
                                Forgot your password?
                            </a>
                        </div>
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('privacy.policy') }}" class="link-text" style="font-size: 13px;">
                            Privacy Policy
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="copyright">
                &copy; 2026 {{ $schoolSettings?->school_name ?? 'TestServes' }} CBT Portal. All rights reserved.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
