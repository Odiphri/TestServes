@extends($layout)

@section('title', $notification->campaign?->title ?? 'Notification')
@section('page-title', $notification->campaign?->title ?? 'Notification')
@section('page-subtitle', 'Open the notification and reply where enabled.')
@section('subtitle', 'Notification conversation')

@section('content')
@php
    $campaign = $notification->campaign;
    $thread = $notification->thread;
    $cardClass = match ($layout) {
        'owner.app' => 'dashboard-card',
        default => 'card',
    };
    $canReply = $campaign?->allows_replies
        && ! $campaign?->is_system_notification
        && (! $campaign?->expires_at || $campaign->expires_at->isFuture())
        && $thread?->status !== 'closed';
@endphp
<style>
    .notification-chat-page { height: calc(100vh - 210px); height: calc(100dvh - 210px); min-height: 520px; display: flex; flex-direction: column; min-height: 0; }
    .notification-chat-panel { min-height: 0; flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .notification-chat-scroll { min-height: 0; flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; padding-right: 4px; }
    .notification-composer { flex: 0 0 auto; padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
    .notification-composer-row { display: flex; gap: 10px; align-items: flex-end; }
    .notification-composer textarea { min-height: 48px; max-height: 140px; resize: none; }
    .notification-send { width: 48px; height: 48px; display: inline-grid; place-items: center; border-radius: 8px; font-weight: 800; }
    @media (max-width: 767.98px) {
        .notification-chat-page { height: calc(100vh - 128px); height: calc(100dvh - 128px); min-height: 420px; }
        .notification-chat-panel { margin-bottom: 0 !important; border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
        .notification-composer { border-top-left-radius: 0; border-top-right-radius: 0; }
    }
</style>

<section class="{{ $cardClass }} mb-3">
    <div class="d-flex justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="h5 mb-1">{{ $campaign?->title ?? 'Notification' }}</h2>
            <p class="text-muted small mb-2">{{ ucfirst($campaign?->type ?? 'general') }} notification</p>
            <p class="mb-0">{{ $campaign?->body }}</p>
        </div>
        @if($campaign?->is_system_notification)
            <span class="badge text-bg-secondary align-self-start">System</span>
        @endif
    </div>
    @if($campaign?->action_url)
        <a class="btn btn-outline-primary btn-sm mt-3" href="{{ $campaign->action_url }}">Open link</a>
    @endif
</section>

<div class="notification-chat-page">
<section class="{{ $cardClass }} mb-3 notification-chat-panel">
    <div class="notification-chat-scroll" data-chat-scroll>
    <div class="d-flex flex-column gap-3" data-notification-thread-messages>
        @forelse($thread?->messages ?? [] as $message)
            @php($isOwner = $message->sender_type === $notification->notifiable_type && (int) $message->sender_id === (int) $notification->notifiable_id)
            <div class="p-3 rounded {{ $isOwner ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5' }}" data-message-id="{{ $message->id }}">
                <div class="fw-bold">{{ $message->sender?->name ?? $message->sender?->full_name ?? ($isOwner ? 'You' : 'Support') }}</div>
                <div style="white-space: pre-wrap;">{{ $message->message }}</div>
                <div class="small {{ $isOwner ? 'text-white-50' : 'text-muted' }} mt-2">{{ $message->created_at->format('M j, Y g:i A') }}</div>
            </div>
        @empty
            <div class="text-muted" data-notification-thread-empty>No replies yet.</div>
        @endforelse
    </div>
    </div>
</section>

@if($canReply)
    <form class="{{ $cardClass }} notification-composer" method="POST" action="{{ route($routePrefix.'.reply', $notification) }}" data-notification-thread-form>
        @csrf
        <div class="notification-composer-row">
        <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="1" maxlength="2000" placeholder="Type your reply" required>{{ old('message') }}</textarea>
        <button class="btn btn-primary notification-send" aria-label="Send reply">&#10148;</button>
        </div>
        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </form>
@else
    <div class="alert alert-secondary">Replies are disabled for this notification.</div>
@endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const threadId = @json($thread?->id);
    const form = document.querySelector('[data-notification-thread-form]');
    const list = document.querySelector('[data-notification-thread-messages]');
    const empty = document.querySelector('[data-notification-thread-empty]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!list) return;

    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    const scrollMessagesToBottom = () => {
        const scroller = list.closest('[data-chat-scroll]') || list.parentElement;
        if (scroller) scroller.scrollTop = scroller.scrollHeight;
    };
    const append = (payload) => {
        if (!payload?.id || list.querySelector(`[data-message-id="${payload.id}"]`)) return;
        const isMine = String(payload.sender_type) === @json($notification->notifiable_type) && Number(payload.sender_id) === Number(@json($notification->notifiable_id));
        const item = document.createElement('div');
        item.className = `p-3 rounded ${isMine ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5'}`;
        item.dataset.messageId = payload.id;
        item.innerHTML = `<div class="fw-bold">${escapeHtml(isMine ? 'You' : (payload.sender_name || 'Support'))}</div><div style="white-space: pre-wrap;">${escapeHtml(payload.message)}</div><div class="small ${isMine ? 'text-white-50' : 'text-muted'} mt-2">${new Date(payload.created_at || Date.now()).toLocaleString()}</div>`;
        empty?.remove();
        list.appendChild(item);
        scrollMessagesToBottom();
    };
    scrollMessagesToBottom();

    const connect = () => {
        if (!threadId || !window.Echo) {
            setTimeout(connect, 300);
            return;
        }
        window.Echo.channel(`notification-thread.${threadId}`).listen('.message.sent', append);
    };
    connect();

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button');
        button?.setAttribute('disabled', 'disabled');
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(csrf ? {'X-CSRF-TOKEN': csrf} : {}) },
                body: new FormData(form),
            });
            if (!response.ok) throw new Error('Reply could not be sent.');
            const data = await response.json();
            append(data.message);
            form.reset();
        } catch (error) {
            alert(error.message || 'Reply could not be sent.');
        } finally {
            button?.removeAttribute('disabled');
        }
    });

    const textarea = form?.querySelector('textarea[name="message"]');
    textarea?.addEventListener('focus', () => {
        setTimeout(() => form.scrollIntoView({ block: 'end', behavior: 'smooth' }), 120);
    });

    window.visualViewport?.addEventListener('resize', () => {
        if (document.activeElement === textarea) {
            form?.scrollIntoView({ block: 'end' });
        }
    });
})();
</script>
@endpush
