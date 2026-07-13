@php
    $platformName = $settings['platform_name'] ?? 'TestServes';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Policy - {{ $platformName }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        body { margin: 0; background: #f6f9fc; color: #102033; font-family: Inter, system-ui, sans-serif; }
        .wrap { width: min(920px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 52px; }
        a { color: #0f766e; font-weight: 800; text-decoration: none; }
        .card { background: #fff; border: 1px solid #dbe3ef; border-radius: 8px; box-shadow: 0 12px 32px rgba(16,32,51,.06); padding: 30px; }
        h1 { font-size: clamp(34px, 5vw, 52px); margin: 0 0 10px; }
        h2 { margin-top: 28px; }
        p, li { color: #4b5f78; line-height: 1.75; }
    </style>
</head>
<body>
    <main class="wrap">
        <p><a href="{{ route('platform.home') }}">{{ $platformName }}</a> / <a href="{{ route('contact') }}">Contact</a></p>
        <article class="card">
            <h1>Security Policy</h1>
            <p>{{ $platformName }} welcomes responsible security reports that help protect schools, students, staff, and platform administrators.</p>

            <h2>Reporting A Vulnerability</h2>
            <p>Please report suspected security issues through the contact page and include enough detail for our team to reproduce and assess the issue. Do not include sensitive student, staff, payment, or school data unless it is strictly necessary.</p>

            <h2>Responsible Disclosure</h2>
            <ul>
                <li>Do not access, modify, delete, or exfiltrate data that does not belong to you.</li>
                <li>Do not disrupt live services, examinations, payments, or school operations.</li>
                <li>Give TestServes reasonable time to investigate and fix confirmed issues before public disclosure.</li>
                <li>Use only accounts, schools, or data you own or have explicit permission to test.</li>
            </ul>

            <h2>Out Of Scope</h2>
            <p>Automated noisy scans, social engineering, denial-of-service testing, physical attacks, and reports without practical security impact are out of scope.</p>

            <h2>Acknowledgments</h2>
            <p>Valid responsible reports may be acknowledged on our <a href="{{ route('security.hall-of-fame') }}">Hall of Fame</a>, where appropriate and with the reporter's consent.</p>
        </article>
    </main>
    @include('partials.public-footer')
    @include('partials.floating-whatsapp')
    @include('partials.cookie-notice')
</body>
</html>
