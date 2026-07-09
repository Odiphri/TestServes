<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Support - TestServes</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: #f6f9fc; font-family: Inter, system-ui, sans-serif; color: #102033; }
        .support-card { width: min(760px, 100%); background: #fff; border: 1px solid #dce5ee; border-radius: 8px; padding: clamp(22px, 5vw, 40px); box-shadow: 0 20px 60px rgba(15,23,42,.08); }
    </style>
</head>
<body>
    <main class="support-card">
        <a href="{{ route('platform.home') }}" class="text-decoration-none">&larr; Back to TestServes</a>
        <h1 class="mt-3">Live Support</h1>
        <p class="text-muted">Chat directly with the TestServes team about anything. This is separate from support tickets.</p>
        @if($school)<div class="alert alert-info">You are chatting from {{ $school->name }}.</div>@endif
        @include('owner.partials.alerts')
        <form method="POST" action="{{ route('live-support.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6"><label class="form-label">Your name</label><input class="form-control" name="visitor_name" value="{{ old('visitor_name') }}" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="visitor_email" value="{{ old('visitor_email') }}"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="visitor_phone" value="{{ old('visitor_phone') }}"></div>
            <div class="col-md-6"><label class="form-label">Subject</label><input class="form-control" name="subject" value="{{ old('subject') }}" required></div>
            <div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required>{{ old('message') }}</textarea></div>
            <div class="col-12"><button class="btn btn-primary">Start chat</button></div>
        </form>
    </main>
</body>
</html>
