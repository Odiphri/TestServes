@php
    $isAdmin = $user?->role === 'admin';
    $platformUrl = rtrim(config('testserves.portal_scheme', 'https').'://'.\App\Support\TestServesDomains::rootDomain(), '/');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Locked - {{ $school?->name ?? 'School Portal' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <section class="card border-0 shadow-sm" style="max-width: 640px;">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-warning mb-3">Portal locked</span>
                @if($isAdmin)
                    <h1 class="h3 fw-bold">Subscription expired or portal deactivated.</h1>
                    <p class="text-muted mb-3">
                        {{ $school?->deactivation_reason ?: 'Please renew your subscription or contact TestServes if you believe this is a mistake.' }}
                    </p>
                    @if($school?->delete_scheduled_at)
                        <p class="text-muted">Deletion is scheduled after {{ $school->delete_scheduled_at->format('M j, Y') }} unless the school is reactivated.</p>
                    @endif
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-primary" href="{{ $platformUrl }}/payments">Resubscribe</a>
                        <a class="btn btn-outline-secondary" href="{{ $platformUrl }}/live-support">Lay dispute</a>
                    </div>
                @else
                    <h1 class="h3 fw-bold">School has been deactivated.</h1>
                    <p class="text-muted mb-4">Please contact your principal or school admin to reactivate the portal.</p>
                    <a class="btn btn-primary" href="{{ \App\Support\TestServesDomains::schoolLoginUrl(request()) }}">Back to login</a>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
