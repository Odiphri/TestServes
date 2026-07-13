@php
    $title = $document?->title ?? $fallback['title'];
    $content = $document?->content ?? $fallback['content'];
    $version = $document?->version ?? $fallback['version'];
    $updated = $document?->updated_at ?? $fallback['updated_at'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - TestServes</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin: 0; background: #f6f9fc; color: #102033; font-family: Inter, system-ui, sans-serif; }
        .policy-wrap { width: min(920px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        .policy-nav { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 24px; }
        .policy-nav a, .policy-body a { color: #0f766e; font-weight: 800; text-decoration: none; }
        .policy-card { background: #fff; border: 1px solid #dbe3ef; border-radius: 8px; box-shadow: 0 12px 32px rgba(16,32,51,.06); overflow: hidden; }
        .policy-header { background: #102033; color: #fff; padding: 30px; }
        .policy-header h1 { font-size: clamp(34px, 5vw, 52px); font-weight: 800; margin: 0; }
        .policy-header p { color: #cbd5e1; margin: 8px 0 0; }
        .policy-body { padding: 30px; }
        .policy-body h2 { font-size: 20px; font-weight: 800; margin-top: 26px; }
        .policy-body p, .policy-body li { color: #4b5f78; line-height: 1.75; }
        .operator-box { border: 1px solid #dbe3ef; border-radius: 8px; background: #f8fafc; padding: 16px; margin-bottom: 22px; }
    </style>
</head>
<body>
    <main class="policy-wrap">
        <nav class="policy-nav">
            <a href="{{ route('platform.home') }}">TestServes</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>
        <article class="policy-card">
            <header class="policy-header">
                <h1>{{ $title }}</h1>
                <p>Version {{ $version }} · Last updated {{ optional($updated)->format('M j, Y') ?? now()->format('M j, Y') }}</p>
            </header>
            <div class="policy-body">
                <div class="operator-box">
                    <strong>Product:</strong> TestServes<br>
                    <strong>Legal operator:</strong> {{ $settings['legal_operator_name'] }}<br>
                    <strong>Contact:</strong> <a href="{{ route('contact') }}">Contact Us</a><br>
                    <strong>WhatsApp:</strong> {{ $settings['support_phone'] }}<br>
                    <strong>Website:</strong> <a href="{{ $settings['website_url'] }}">{{ $settings['website_url'] }}</a><br>
                    {{ $settings['legal_operator_statement'] }}
                </div>
                {!! $content !!}
            </div>
        </article>
    </main>
    @include('partials.public-footer')
    @include('partials.floating-whatsapp')
    @include('partials.cookie-notice')
</body>
</html>
