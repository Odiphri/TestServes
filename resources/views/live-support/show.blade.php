<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conversation->reference }} - Live Support</title>
    <link rel="icon" href="{{ \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg') }}">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        html { height: 100%; }
        body { min-height: 100vh; min-height: 100dvh; margin: 0; padding: 24px; background: #f6f9fc; font-family: Inter, system-ui, sans-serif; color: #102033; overflow: hidden; }
        .support-shell { width: min(860px, 100%); height: calc(100vh - 48px); height: calc(100dvh - 48px); margin: 0 auto; display: flex; flex-direction: column; min-height: 0; }
        .support-card { background: #fff; border: 1px solid #dce5ee; border-radius: 8px; padding: 22px; box-shadow: 0 20px 60px rgba(15,23,42,.08); }
        .support-chat-panel { min-height: 0; flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .support-chat-scroll { min-height: 0; flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; padding-right: 4px; }
        .support-composer { flex: 0 0 auto; padding-bottom: max(22px, env(safe-area-inset-bottom)); }
        .support-composer-row { display: flex; gap: 10px; align-items: flex-end; }
        .support-composer textarea { min-height: 48px; max-height: 140px; resize: none; }
        .support-send { width: 48px; height: 48px; display: inline-grid; place-items: center; border-radius: 8px; font-weight: 800; }
        @media (max-width: 640px) {
            body { padding: 0; }
            .support-shell { width: 100%; height: 100vh; height: 100dvh; }
            .support-card { border-radius: 0; box-shadow: none; }
            .support-card:first-child { padding: 16px; }
            .support-card:first-child h1 { font-size: 1.35rem; }
            .support-chat-panel { margin-bottom: 0 !important; border-bottom: 0; }
            .support-composer { border-top: 0; }
        }
    </style>
</head>
<body>
    <main class="support-shell">
        <div class="support-card mb-3">
            <a href="{{ route('platform.home') }}" class="text-decoration-none">&larr; Back to TestServes</a>
            <h1 class="mt-3">Live Support</h1>
            <p class="text-muted mb-0">{{ $conversation->reference }} &middot; {{ ucfirst($conversation->status) }}</p>
        </div>
        @include('owner.partials.alerts')
        <div class="support-card support-chat-panel mb-3">
            <div class="support-chat-scroll" data-chat-scroll>
            <div class="d-flex flex-column gap-3" data-live-support-messages>
                @forelse($conversation->messages as $message)
                    <div class="p-3 rounded {{ $message->sender_type === 'admin' ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5' }}" data-message-id="{{ $message->id }}">
                        <div class="fw-bold">{{ $message->sender_name ?? ucfirst($message->sender_type) }}</div>
                        <div style="white-space: pre-wrap;">{{ $message->message }}</div>
                        <div class="small {{ $message->sender_type === 'admin' ? 'text-white-50' : 'text-muted' }} mt-2">{{ $message->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                @empty
                    <div class="text-muted" data-live-support-empty>No messages yet.</div>
                @endforelse
            </div>
            </div>
        </div>
        @if($conversation->status === 'closed')
            <div class="alert alert-secondary">This chat has been closed.</div>
        @else
            <form class="support-card support-composer" method="POST" action="{{ route('live-support.reply', $conversation->access_token) }}" data-live-support-form>
                @csrf
                <div class="support-composer-row">
                    <textarea class="form-control" name="message" rows="1" placeholder="Type your message" required></textarea>
                    <button class="btn btn-primary support-send" aria-label="Send message">&#10148;</button>
                </div>
            </form>
        @endif
    </main>
    @include('live-support.partials.realtime-chat', [
        'channelName' => 'live-support-token.'.$conversation->access_token,
        'channelType' => 'public',
    ])
    <script>
    (() => {
        const key = 'testserves.liveSupportConversations';
        const sessions = JSON.parse(localStorage.getItem(key) || '[]').filter((item) => item?.token !== @json($conversation->access_token));
        sessions.push({
            token: @json($conversation->access_token),
            url: @json(route('live-support.show', $conversation->access_token)),
            subject: @json($conversation->subject ?: $conversation->reference),
            created_at: @json(optional($conversation->created_at)->format('M j, Y g:i A')),
        });
        localStorage.setItem(key, JSON.stringify(sessions.slice(-10)));

        const composer = document.querySelector('[data-live-support-form]');
        const textarea = composer?.querySelector('textarea[name="message"]');
        textarea?.addEventListener('focus', () => {
            setTimeout(() => composer.scrollIntoView({ block: 'end', behavior: 'smooth' }), 120);
        });

        window.visualViewport?.addEventListener('resize', () => {
            if (document.activeElement === textarea) {
                composer?.scrollIntoView({ block: 'end' });
            }
        });
    })();
    </script>
</body>
</html>
