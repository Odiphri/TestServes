@php
    $hideNotificationBell = Auth::guard('platform_admin')->check();
    $notificationRoutePrefix = $notificationRoutePrefix
        ?? (Auth::guard('school_owner')->check() ? 'platform.notifications' : 'notifications');

    $notificationCenter = app(\App\Support\NotificationCenter::class);
    $notificationPreview = collect();
    $notificationUnreadCount = 0;
    $ownerNotificationUserId = Auth::guard('school_owner')->id();

    try {
        $notificationPreview = $notificationCenter->latestForCurrentUser(request(), 5);
        $notificationUnreadCount = $notificationCenter->unreadCountForCurrentUser(request());
    } catch (\Throwable $exception) {
        $notificationPreview = collect();
        $notificationUnreadCount = 0;
    }
@endphp

@unless($hideNotificationBell)
<style>
    .notification-bell { position: relative; }
    .notification-bell .notification-trigger {
        width: 38px;
        height: 38px;
        display: inline-grid;
        place-items: center;
        border-radius: 8px;
    }
    .notification-count {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        display: grid;
        place-items: center;
        padding: 0 5px;
    }
    .notification-menu {
        width: min(360px, calc(100vw - 32px));
        max-height: 420px;
        overflow: auto;
        border-radius: 8px;
    }
    .notification-item {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        max-width: 100%;
    }
    .notification-item.unread { background: #eff6ff; }
    .notification-item strong { display: block; color: #0f172a; }
    .notification-item span { display: block; color: #64748b; font-size: 12px; line-height: 1.45; }
    @media (max-width: 576px) {
        .notification-menu {
            position: fixed !important;
            inset: auto 12px auto 12px !important;
            width: auto;
            max-width: calc(100vw - 24px);
            transform: none !important;
        }
    }
</style>

<div class="dropdown notification-bell">
    <button class="btn btn-outline-secondary btn-sm notification-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="fas fa-bell" aria-hidden="true"></i>
        @if($notificationUnreadCount > 0)
            <span class="notification-count">{{ $notificationUnreadCount > 99 ? '99+' : $notificationUnreadCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-menu p-0" data-notification-menu>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong class="small">Notifications</strong>
            @if($notificationUnreadCount > 0)
                <form method="POST" action="{{ route($notificationRoutePrefix.'.read-all') }}">
                    @csrf
                    <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                </form>
            @endif
        </div>

        <div data-notification-preview-list>
        @forelse($notificationPreview as $notification)
            <a class="dropdown-item notification-item py-2 {{ $notification->read_at ? '' : 'unread' }}" href="{{ route($notificationRoutePrefix.'.index') }}">
                <strong>{{ $notification->campaign?->title ?? 'Notification' }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($notification->campaign?->body ?? '', 90) }}</span>
                <span>{{ optional($notification->delivered_at)->diffForHumans() ?? 'Just now' }}</span>
            </a>
        @empty
            <div class="px-3 py-4 text-center text-muted small" data-notification-empty>No notifications yet.</div>
        @endforelse
        </div>

        <div class="border-top p-2">
            <a class="btn btn-primary btn-sm w-100" href="{{ route($notificationRoutePrefix.'.index') }}">View all</a>
        </div>
    </div>
</div>
@if($ownerNotificationUserId)
<script>
(() => {
    const ownerId = @json($ownerNotificationUserId);
    const list = document.querySelector('[data-notification-preview-list]');
    const empty = document.querySelector('[data-notification-empty]');
    const trigger = document.querySelector('.notification-trigger');
    const indexUrl = @json(route($notificationRoutePrefix.'.index'));
    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

    const connect = () => {
        if (!window.Echo || !ownerId || !list) {
            setTimeout(connect, 300);
            return;
        }

        window.Echo.channel(`owner-notifications.${ownerId}`).listen('.notification.delivered', (payload) => {
            empty?.remove();
            const item = document.createElement('a');
            item.className = 'dropdown-item notification-item py-2 unread';
            item.href = indexUrl;
            item.innerHTML = `<strong>${escapeHtml(payload.title)}</strong><span>${escapeHtml(String(payload.body || '').slice(0, 90))}</span><span>Just now</span>`;
            list.prepend(item);

            let badge = trigger?.querySelector('.notification-count');
            if (!badge && trigger) {
                badge = document.createElement('span');
                badge.className = 'notification-count';
                trigger.appendChild(badge);
            }
            if (badge) {
                const current = Number.parseInt(badge.textContent || '0', 10) || 0;
                badge.textContent = current >= 99 ? '99+' : String(current + 1);
            }
        });
    };

    connect();
})();
</script>
@endif
@endunless
