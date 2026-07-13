@extends($layout)

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Read messages and reply when a notification allows it.')
@section('subtitle', 'Read platform messages and reply where enabled.')

@push('styles')
<style>
    .notification-center-card {
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .notification-center-card > .d-flex > div:first-child {
        min-width: 0;
        flex: 1 1 260px;
    }
    .notification-center-card p,
    .notification-center-card h2,
    .notification-center-card a {
        max-width: 100%;
    }
    @media (max-width: 576px) {
        .notification-center-card {
            padding: 14px !important;
        }
        .notification-center-card > .d-flex {
            align-items: stretch !important;
        }
        .notification-center-card > .d-flex form,
        .notification-center-card > .d-flex .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $notificationCardClass = match ($layout) {
        'super-admin.layout' => 'platform-card',
        'owner.app' => 'dashboard-card',
        default => 'card',
    };
@endphp

<div class="{{ $notificationCardClass }} notification-center-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <div>
            <h2 class="h5 mb-1">Notification center</h2>
            <p class="text-muted mb-0">System notices are read-only. General notices may allow replies.</p>
        </div>
        <form method="POST" action="{{ route($routePrefix.'.read-all') }}">
            @csrf
            <button class="btn btn-outline-primary btn-sm">Mark all read</button>
        </form>
    </div>
</div>

@forelse($notifications as $notification)
    @php
        $campaign = $notification->campaign;
    @endphp
    <article class="{{ $notificationCardClass }} notification-center-card p-3 mb-3">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h2 class="h5 mb-0">{{ $campaign?->title ?? 'Notification' }}</h2>
                    @if(! $notification->read_at)
                        <span class="badge text-bg-primary">Unread</span>
                    @endif
                    @if($campaign?->is_system_notification)
                        <span class="badge text-bg-secondary">System</span>
                    @endif
                </div>
                <p class="text-muted small mb-2">{{ optional($notification->delivered_at)->format('M j, Y g:i A') ?? 'Delivered recently' }}</p>
                <p class="mb-2">{{ $campaign?->body }}</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-outline-primary btn-sm" href="{{ route($routePrefix.'.show', $notification) }}">Open chat</a>
                @if(! $notification->read_at)
                    <form method="POST" action="{{ route($routePrefix.'.read', $notification) }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Mark read</button>
                    </form>
                @endif
                <form method="POST" action="{{ route($routePrefix.'.destroy', $notification) }}" onsubmit="return confirm('Delete this notification from your inbox?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </article>
@empty
    <div class="{{ $notificationCardClass }} p-4 text-center text-muted">
        No notifications yet.
    </div>
@endforelse

{{ $notifications->links() }}
@endsection
