<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $schoolIcon = $schoolSettings?->logo_path ? asset('storage/' . $schoolSettings->logo_path) : asset('images/default-school-icon.svg');
    @endphp
    <title>{{ $schoolSettings?->school_name ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ $schoolIcon }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ $schoolIcon }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .navbar .container {
            gap: 10px;
        }

        .navbar-brand,
        #navbarDropdown {
            max-width: min(68vw, 320px);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .navbar-collapse {
            min-width: 0;
        }

        @media (max-width: 767.98px) {
            .navbar .container {
                align-items: flex-start;
            }

            .navbar-toggler {
                flex: 0 0 auto;
            }

            .navbar-collapse {
                width: 100%;
            }

            #navbarDropdown {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @unless(request()->routeIs('*.dashboard'))
                        <li class="nav-item">
                            <button type="button" class="btn btn-outline-secondary btn-sm my-2 my-md-0" onclick="history.length > 1 ? history.back() : window.location.assign('{{ url('/') }}')">
                                Back
                            </button>
                        </li>
                        @endunless
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->full_name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Logout') }}
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
