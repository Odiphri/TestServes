<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Support - TestServes</title>
    <link rel="icon" href="{{ \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg') }}">
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
        <div class="border rounded p-3 mb-3 d-none" data-live-support-history>
            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                <strong>Previous chats on this browser</strong>
                <button class="btn btn-sm btn-outline-danger" type="button" data-clear-live-support-history>End all local sessions</button>
            </div>
            <div class="list-group" data-live-support-history-list></div>
            <button class="btn btn-sm btn-primary mt-3" type="button" data-start-new-live-support>Start new session</button>
        </div>
        <form method="POST" action="{{ route('live-support.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6"><label class="form-label">Your name</label><input class="form-control" name="visitor_name" value="{{ old('visitor_name', $owner?->name) }}" @if(! $owner) required @endif></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="visitor_email" value="{{ old('visitor_email', $owner?->email) }}"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="visitor_phone" value="{{ old('visitor_phone', $owner?->phone) }}"></div>
            <div class="col-md-6"><label class="form-label">Subject</label><input class="form-control" name="subject" value="{{ old('subject') }}" required></div>
            <div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required>{{ old('message') }}</textarea></div>
            <div class="col-12"><button class="btn btn-primary">Start chat</button></div>
        </form>
    </main>
    <script>
    (() => {
        const key = 'testserves.liveSupportConversations';
        const history = document.querySelector('[data-live-support-history]');
        const list = document.querySelector('[data-live-support-history-list]');
        const form = document.querySelector('form[action="{{ route('live-support.store') }}"]');
        const sessions = JSON.parse(localStorage.getItem(key) || '[]').filter((item) => item?.token);

        if (sessions.length && history && list) {
            history.classList.remove('d-none');
            form?.classList.add('d-none');
            sessions.slice(-5).reverse().forEach((item) => {
                const link = document.createElement('a');
                link.className = 'list-group-item list-group-item-action';
                link.href = item.url;
                link.innerHTML = `<strong>${item.subject || 'Live support chat'}</strong><div class="small text-muted">${item.created_at || ''}</div>`;
                list.appendChild(link);
            });
        }

        document.querySelector('[data-start-new-live-support]')?.addEventListener('click', () => form?.classList.remove('d-none'));
        document.querySelector('[data-clear-live-support-history]')?.addEventListener('click', () => {
            localStorage.removeItem(key);
            window.location.reload();
        });
    })();
    </script>
</body>
</html>
