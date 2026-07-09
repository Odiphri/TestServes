<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $conversation->reference }} - Live Support</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; margin: 0; padding: 24px; background: #f6f9fc; font-family: Inter, system-ui, sans-serif; color: #102033; }
        .support-shell { width: min(860px, 100%); margin: 0 auto; }
        .support-card { background: #fff; border: 1px solid #dce5ee; border-radius: 8px; padding: 22px; box-shadow: 0 20px 60px rgba(15,23,42,.08); }
    </style>
</head>
<body>
    <main class="support-shell">
        <div class="support-card mb-3">
            <a href="{{ route('platform.home') }}" class="text-decoration-none">&larr; Back to TestServes</a>
            <h1 class="mt-3">Live Support</h1>
            <p class="text-muted mb-0">{{ $conversation->reference }} · {{ ucfirst($conversation->status) }}</p>
        </div>
        @include('owner.partials.alerts')
        <div class="support-card mb-3">
            <div class="d-flex flex-column gap-3">
                @foreach($conversation->messages as $message)
                    <div class="p-3 rounded {{ $message->sender_type === 'admin' ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5' }}">
                        <div class="fw-bold">{{ $message->sender_name ?? ucfirst($message->sender_type) }}</div>
                        <div style="white-space: pre-wrap;">{{ $message->message }}</div>
                        <div class="small {{ $message->sender_type === 'admin' ? 'text-white-50' : 'text-muted' }} mt-2">{{ $message->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @if($conversation->status === 'closed')
            <div class="alert alert-secondary">This chat has been closed.</div>
        @else
            <form class="support-card" method="POST" action="{{ route('live-support.reply', $conversation->access_token) }}">
                @csrf
                <label class="form-label">Reply</label>
                <textarea class="form-control mb-3" name="message" rows="4" required></textarea>
                <button class="btn btn-primary">Send message</button>
            </form>
        @endif
    </main>
</body>
</html>
