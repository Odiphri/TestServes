@php
    $platformUrl = rtrim(config('testserves.portal_scheme', 'https').'://'.\App\Support\TestServesDomains::rootDomain(), '/');
    $deadlineLabel = fn ($date) => $date ? $date->format('M j, Y \\b\\y g:i:s A') : 'the due date';
    $messages = [
        'pending_payment' => [
            'title' => 'Payment required to activate this portal.',
            'body' => 'Complete payment to unlock full access.',
            'primary' => ['label' => 'Make Payment', 'url' => $platformUrl.'/payments'],
            'secondary' => ['label' => 'Start Free Trial', 'url' => $platformUrl.'/payments'],
        ],
        'trial_expired' => [
            'title' => 'Your access has expired.',
            'body' => 'Your trial ended on '.$deadlineLabel($school?->trial_ends_at ?: $school?->subscription_expires_at).'. Renew to continue.',
            'primary' => ['label' => 'Renew Now', 'url' => $platformUrl.'/payments'],
        ],
        'subscription_expired' => [
            'title' => 'Your access has expired.',
            'body' => 'Your subscription ended on '.$deadlineLabel($school?->subscription_ends_at ?: $school?->subscription_expires_at).'. Renew to continue.',
            'primary' => ['label' => 'Renew Now', 'url' => $platformUrl.'/payments'],
        ],
        'suspended' => [
            'title' => 'This school portal has been suspended.',
            'body' => 'Reason: '.($school?->suspension_reason ?: $school?->deactivation_reason ?: 'No reason provided.').' Contact your school administrator or TestServes support.',
        ],
        'deactivated' => [
            'title' => 'This school portal has been deactivated.',
            'body' => 'Contact TestServes admin if you believe this is an error.',
        ],
        'setup_incomplete' => [
            'title' => 'Portal Setup Not Complete',
            'body' => 'This school portal has not finished setup yet. Please contact TestServes support.',
        ],
        'session_expired' => [
            'title' => 'Portal access changed.',
            'body' => 'Your session was ended because this school portal access changed. Please refresh or contact support.',
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
    @include('partials.app-icons')
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
                    @if(isset($message['primary']))
                        <a class="btn btn-primary" href="{{ $message['primary']['url'] }}">{{ $message['primary']['label'] }}</a>
                    @endif
                    @if(isset($message['secondary']))
                        <a class="btn btn-outline-primary" href="{{ $message['secondary']['url'] }}">{{ $message['secondary']['label'] }}</a>
                    @endif
                    <a class="btn btn-outline-secondary" href="mailto:testserves.ng@gmail.com">Contact Support</a>
                    <a class="btn btn-outline-secondary" href="{{ $platformUrl }}">Go to testserves.com</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
