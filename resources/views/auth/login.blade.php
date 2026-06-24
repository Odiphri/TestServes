<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $schoolName = $schoolSettings?->school_name ?? 'TOKE Schools';
        $schoolIcon = $schoolSettings?->logo_path ? asset('storage/' . $schoolSettings->logo_path) : asset('images/default-school-icon.svg');
    @endphp
    <title>{{ $schoolSettings?->school_name ?? 'TOKE Schools' }} CBT Portal - Login</title>
    <link rel="icon" href="{{ $schoolIcon }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ $schoolIcon }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9c4d2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: #0a1931;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            color: #0a1931;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #0a1931;
            box-shadow: 0 0 0 0.2rem rgba(10, 25, 49, 0.25);
        }
        
        .btn-login {
            background: #0a1931;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #1a2941;
            transform: translateY(-1px);
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-weight: bold;
            color: #0a1931;
            font-size: 24px;
            overflow: hidden;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .form-check-input:checked {
            background-color: #0a1931;
            border-color: #0a1931;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="school-logo">
                    <img src="{{ $schoolIcon }}" alt="{{ $schoolName }} logo">
                </div>
                <h1>{{ $schoolSettings?->school_name ?? 'CBT Portal' }}</h1>
                <p>{{ $schoolSettings?->motto ?: 'Computer Based Testing System' }}</p>
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
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="mb-3">
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
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               placeholder="Enter your password"
                               required 
                               autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-login">
                            Login to Portal
                        </button>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-center mt-3">
                            <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: #0a1931;">
                                Forgot your password?
                            </a>
                        </div>
                    @endif

                    <div class="text-center mt-2">
                        <a href="{{ route('privacy.policy') }}" class="text-decoration-none" style="color: #0a1931;">
                            Privacy Policy
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted" style="color: #0a1931;">
                © 2026 {{ $schoolSettings?->school_name ?? 'TOKE Schools' }} CBT Portal. All rights reserved.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
