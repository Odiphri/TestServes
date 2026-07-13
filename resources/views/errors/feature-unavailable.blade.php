<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feature Not Available</title>
    @include('partials.app-icons')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @php($platformUrl = rtrim(config('testserves.portal_scheme', 'https').'://'.\App\Support\TestServesDomains::rootDomain(), '/'))
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <section class="card border-0 shadow-sm" style="max-width: 560px;">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-secondary mb-3">Plan feature</span>
                <h1 class="h3 fw-bold">Feature Not Available On Your Plan</h1>
                <p class="text-muted mb-4">
                    {{ $feature ?? 'This feature' }} is not included in your current TestServes plan.
                    Please upgrade your subscription to unlock it.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary" href="{{ $platformUrl }}/plans">View Plans</a>
                    <a class="btn btn-outline-secondary" href="{{ $platformUrl }}/live-support">Contact TestServes</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
