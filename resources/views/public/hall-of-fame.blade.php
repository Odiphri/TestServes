@php
    $platformName = $settings['platform_name'] ?? 'TestServes';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Hall of Fame - {{ $platformName }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        body { margin: 0; background: #f6f9fc; color: #102033; font-family: Inter, system-ui, sans-serif; }
        .wrap { width: min(820px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 52px; }
        a { color: #0f766e; font-weight: 800; text-decoration: none; }
        .card { background: #fff; border: 1px solid #dbe3ef; border-radius: 8px; box-shadow: 0 12px 32px rgba(16,32,51,.06); padding: 30px; }
        h1 { font-size: clamp(34px, 5vw, 52px); margin: 0 0 10px; }
        p { color: #4b5f78; line-height: 1.75; }
    </style>
</head>
<body>
    <main class="wrap">
        <p><a href="{{ route('platform.home') }}">{{ $platformName }}</a> / <a href="{{ route('security.policy') }}">Security Policy</a></p>
        <article class="card">
            <h1>Security Hall of Fame</h1>
            <p>We appreciate responsible researchers who help keep {{ $platformName }} safe. Public acknowledgments will appear here after a report is validated, remediated, and approved for disclosure.</p>
            <p>No public acknowledgments have been published yet.</p>
        </article>
    </main>
    @include('partials.public-footer')
    @include('partials.floating-whatsapp')
    @include('partials.cookie-notice')
</body>
</html>
