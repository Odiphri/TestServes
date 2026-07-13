<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Portal Not Found - TestServes</title>
    @include('partials.app-icons')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @php($platformUrl = rtrim(config('testserves.portal_scheme', 'https').'://'.\App\Support\TestServesDomains::rootDomain(), '/'))
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <section class="card border-0 shadow-sm" style="max-width: 560px;">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-danger mb-3">404</span>
                <h1 class="h3 fw-bold">School Portal Not Found</h1>
                <p class="text-muted mb-4">
                    This school portal did not make the register. Please check the portal link or contact TestServes support.
                </p>
                <a class="btn btn-primary" href="{{ $platformUrl }}">Go to TestServes Home</a>
            </div>
        </section>
    </main>
</body>
</html>
