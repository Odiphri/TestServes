@extends($layout)

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Read messages and reply when a notification allows it.')
@section('subtitle', 'Read platform messages and reply where enabled.')

@section('content')
@php
    $notificationCardClass = match ($layout) {
        'super-admin.layout' => 'platform-card',
        'owner.app' => 'dashboard-card',
        default => 'card',
    };
@endphp

<div class="{{ $notificationCardClass }} p-3 mb-3">
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
        $thread = $notification->thread;
        $canReply = $campaign?->allows_replies
            && ! $campaign?->is_system_notification
            && (! $campaign?->expires_at || $campaign->expires_at->isFuture())
            && $thread?->status !== 'closed';
    @endphp
    <article class="{{ $notificationCardClass }} p-3 mb-3">
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
                @if($campaign?->action_url)
                    <a class="btn btn-outline-primary btn-sm" href="{{ $campaign->action_url }}">Open link</a>
                @endif
            </div>
            @if(! $notification->read_at)
                <form method="POST" action="{{ route($routePrefix.'.read', $notification) }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm">Mark read</button>
                </form>
            @endif
        </div>

        @if($thread?->messages?->isNotEmpty())
            <div class="border-top mt-3 pt-3">
                <h3 class="h6">Replies</h3>
                @foreach($thread->messages as $message)
                    <div class="mb-2">
                        <p class="mb-1">{{ $message->message }}</p>
                        <small class="text-muted">{{ optional($message->created_at)->format('M j, Y g:i A') }}</small>
                    </div>
                @endforeach
            </div>
        @endif

        @if($canReply)
            <form class="border-top mt-3 pt-3" method="POST" action="{{ route($routePrefix.'.reply', $notification) }}">
                @csrf
                <label class="form-label">Reply</label>
                <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="3" maxlength="2000" required>{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button class="btn btn-primary btn-sm mt-2">Send reply</button>
            </form>
        @endif
    </article>
@empty
    <div class="{{ $notificationCardClass }} p-4 text-center text-muted">
        No notifications yet.
    </div>
@endforelse

{{ $notifications->links() }}
@endsection
