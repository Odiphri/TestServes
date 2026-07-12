@php
    $notificationRoutePrefix = $notificationRoutePrefix
        ?? (Auth::guard('platform_admin')->check()
            ? 'super-admin.notifications'
            : (Auth::guard('school_owner')->check() ? 'platform.notifications' : 'notifications'));

    $notificationCenter = app(\App\Support\NotificationCenter::class);
    $notificationPreview = collect();
    $notificationUnreadCount = 0;

    try {
        $notificationPreview = $notificationCenter->latestForCurrentUser(request(), 5);
        $notificationUnreadCount = $notificationCenter->unreadCountForCurrentUser(request());
    } catch (\Throwable $exception) {
        $notificationPreview = collect();
        $notificationUnreadCount = 0;
    }
@endphp

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
    .notification-item { white-space: normal; }
    .notification-item.unread { background: #eff6ff; }
    .notification-item strong { display: block; color: #0f172a; }
    .notification-item span { display: block; color: #64748b; font-size: 12px; line-height: 1.45; }
</style>

<div class="dropdown notification-bell">
    <button class="btn btn-outline-secondary btn-sm notification-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="fas fa-bell" aria-hidden="true"></i>
        @if($notificationUnreadCount > 0)
            <span class="notification-count">{{ $notificationUnreadCount > 99 ? '99+' : $notificationUnreadCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong class="small">Notifications</strong>
            @if($notificationUnreadCount > 0)
                <form method="POST" action="{{ route($notificationRoutePrefix.'.read-all') }}">
                    @csrf
                    <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                </form>
            @endif
        </div>

        @forelse($notificationPreview as $notification)
            <a class="dropdown-item notification-item py-2 {{ $notification->read_at ? '' : 'unread' }}" href="{{ route($notificationRoutePrefix.'.index') }}">
                <strong>{{ $notification->campaign?->title ?? 'Notification' }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($notification->campaign?->body ?? '', 90) }}</span>
                <span>{{ optional($notification->delivered_at)->diffForHumans() ?? 'Just now' }}</span>
            </a>
        @empty
            <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
        @endforelse

        <div class="border-top p-2">
            <a class="btn btn-primary btn-sm w-100" href="{{ route($notificationRoutePrefix.'.index') }}">View all</a>
        </div>
    </div>
</div>
