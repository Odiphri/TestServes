@extends('super-admin.layout')

@section('title', 'Notification Conversation')
@section('subtitle', ($recipient->notifiable?->name ?? 'Owner').' - '.($recipient->campaign?->title ?? 'Notification'))

@section('content')
<style>
    .notification-chat-page { height: calc(100vh - 190px); min-height: 540px; display: flex; flex-direction: column; }
    .notification-chat-panel { min-height: 0; flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .notification-chat-scroll { min-height: 0; flex: 1; overflow-y: auto; padding-right: 4px; }
    .notification-composer { flex: 0 0 auto; }
    .notification-composer-row { display: flex; gap: 10px; align-items: flex-end; }
    .notification-composer textarea { min-height: 48px; max-height: 140px; resize: none; }
    .notification-send { width: 48px; height: 48px; display: inline-grid; place-items: center; border-radius: 8px; font-weight: 800; }
    @media (max-width: 767.98px) {
        .notification-chat-page { height: calc(100vh - 170px); min-height: 480px; }
    }
</style>
<section class="platform-card p-3 mb-3">
    <h2 class="h5 mb-1">{{ $recipient->campaign?->title }}</h2>
    <p class="mb-0">{{ $recipient->campaign?->body }}</p>
</section>

<div class="notification-chat-page">
<section class="platform-card p-3 mb-3 notification-chat-panel">
    <div class="notification-chat-scroll" data-chat-scroll>
    <div class="d-flex flex-column gap-3" data-notification-thread-messages>
        @forelse($recipient->thread?->messages ?? [] as $message)
            @php($isAdmin = str_contains($message->sender_type, 'PlatformAdmin'))
            <div class="p-3 rounded {{ $isAdmin ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5' }}" data-message-id="{{ $message->id }}">
                <div class="fw-bold">{{ $message->sender?->name ?? ($isAdmin ? 'Admin' : ($recipient->notifiable?->name ?? 'Owner')) }}</div>
                <div style="white-space: pre-wrap;">{{ $message->message }}</div>
                <div class="small {{ $isAdmin ? 'text-white-50' : 'text-muted' }} mt-2">{{ $message->created_at->format('M j, Y g:i A') }}</div>
            </div>
        @empty
            <div class="text-muted">No messages yet.</div>
        @endforelse
    </div>
    </div>
</section>

<form class="platform-card p-3 notification-composer" method="POST" action="{{ route('super-admin.notification-campaigns.thread.reply', $recipient) }}" data-notification-thread-form>
    @csrf
    <div class="small text-muted mb-2">Reply to {{ $recipient->notifiable?->name ?? 'owner' }}</div>
    <div class="notification-composer-row">
        <textarea class="form-control" name="message" rows="1" maxlength="2000" placeholder="Type your reply" required></textarea>
        <button class="btn btn-primary notification-send" aria-label="Send reply">&#10148;</button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const threadId = @json($recipient->thread?->id);
    const form = document.querySelector('[data-notification-thread-form]');
    const list = document.querySelector('[data-notification-thread-messages]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!list || !threadId) return;

    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    const scrollMessagesToBottom = () => {
        const scroller = list.closest('[data-chat-scroll]') || list.parentElement;
        if (scroller) scroller.scrollTop = scroller.scrollHeight;
    };
    const append = (payload) => {
        if (!payload?.id || list.querySelector(`[data-message-id="${payload.id}"]`)) return;
        const isAdmin = String(payload.sender_type || '').includes('PlatformAdmin');
        const item = document.createElement('div');
        item.className = `p-3 rounded ${isAdmin ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5'}`;
        item.dataset.messageId = payload.id;
        item.innerHTML = `<div class="fw-bold">${escapeHtml(isAdmin ? (payload.sender_name || 'Admin') : (payload.sender_name || 'Owner'))}</div><div style="white-space: pre-wrap;">${escapeHtml(payload.message)}</div><div class="small ${isAdmin ? 'text-white-50' : 'text-muted'} mt-2">${new Date(payload.created_at || Date.now()).toLocaleString()}</div>`;
        list.appendChild(item);
        scrollMessagesToBottom();
    };
    scrollMessagesToBottom();

    const connect = () => {
        if (!window.Echo) {
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
})();
</script>
@endpush
