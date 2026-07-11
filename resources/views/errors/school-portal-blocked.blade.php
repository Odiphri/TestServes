@php
    $platformUrl = rtrim(config('testserves.portal_scheme', 'https').'://'.\App\Support\TestServesDomains::rootDomain(), '/');
    $messages = [
        'pending_payment' => [
            'title' => 'Payment Required',
            'body' => 'This portal is not open yet. Start a trial or complete payment approval to enter the CBT app.',
        ],
        'trial_expired' => [
            'title' => 'Trial Expired',
            'body' => 'Your free trial has finished. Please renew your subscription or contact TestServes to continue.',
        ],
        'subscription_expired' => [
            'title' => 'Subscription Expired',
            'body' => 'This portal subscription has expired. Please renew your subscription to continue using the school portal.',
        ],
        'suspended' => [
            'title' => 'Portal Suspended',
            'body' => 'This school portal is currently suspended. Please contact TestServes support.',
        ],
        'deactivated' => [
            'title' => 'Portal Deactivated',
            'body' => 'This school portal is deactivated. Please contact TestServes support.',
        ],
        'setup_incomplete' => [
            'title' => 'Portal Setup Not Complete',
            'body' => 'This school portal has not finished setup yet. Please contact TestServes support.',
        ],
    ];
    $message = $messages[$reason] ?? $messages['pending_payment'];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $message['title'] }} - {{ $school?->name ?? 'School Portal' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <section class="card border-0 shadow-sm" style="max-width: 600px;">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-warning mb-3">Portal locked</span>
                <h1 class="h3 fw-bold">{{ $message['title'] }}</h1>
                <p class="text-muted mb-4">{{ $message['body'] }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary" href="{{ $platformUrl }}/login">Owner Login</a>
                    <a class="btn btn-outline-secondary" href="{{ $platformUrl }}/live-support">Contact Support</a>
                    <a class="btn btn-outline-secondary" href="{{ $platformUrl }}">Go Home</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
