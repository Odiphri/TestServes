@extends('super-admin.layout')

@section('title', 'Live Support Chat')
@section('subtitle', $conversation->reference)

@section('content')
<style>
    .support-chat-page { height: calc(100vh - 170px); min-height: 520px; }
    .support-chat-column { height: 100%; min-height: 0; display: flex; flex-direction: column; }
    .support-chat-panel { min-height: 0; flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .support-chat-scroll { min-height: 0; flex: 1; overflow-y: auto; padding-right: 4px; }
    .support-composer { flex: 0 0 auto; }
    .support-composer-row { display: flex; gap: 10px; align-items: flex-end; }
    .support-composer textarea { min-height: 48px; max-height: 140px; resize: none; }
    .support-send { width: 48px; height: 48px; display: inline-grid; place-items: center; border-radius: 8px; font-weight: 800; }
    @media (max-width: 991.98px) {
        .support-chat-page { height: auto; min-height: 0; }
        .support-chat-column { height: min(72vh, 680px); }
    }
</style>
<div class="row g-3 support-chat-page">
    <div class="col-lg-8 support-chat-column">
        <div class="platform-card p-3 mb-3">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $conversation->subject ?? 'Live support conversation' }}</h2>
                    <div class="text-muted">{{ $conversation->visitor_name ?? 'Visitor' }} &middot; {{ $conversation->visitor_email ?? 'No email' }}</div>
                </div>
                <span class="status-badge status-{{ $conversation->status }} align-self-start">{{ ucfirst($conversation->status) }}</span>
            </div>
        </div>

        <div class="platform-card p-3 mb-3 support-chat-panel">
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

        @if($conversation->status !== 'closed')
            <form class="platform-card p-3 support-composer" method="POST" action="{{ route('super-admin.live-support.reply', $conversation) }}" data-live-support-form>
                @csrf
                <div class="support-composer-row">
                    <textarea class="form-control" name="message" rows="1" placeholder="Type your reply" required></textarea>
                    <button class="btn btn-primary support-send" aria-label="Send reply">&#10148;</button>
                </div>
            </form>
        @endif
    </div>
    <div class="col-lg-4">
        <form class="platform-card p-3" method="POST" action="{{ route('super-admin.live-support.update', $conversation) }}">
            @csrf
            @method('PATCH')
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    @foreach(['open','waiting','answered','closed'] as $status)
                        <option value="{{ $status }}" @selected($conversation->status===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority">
                    @foreach(['low','medium','high'] as $priority)
                        <option value="{{ $priority }}" @selected($conversation->priority===$priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Assign support admin</label>
                <select class="form-select" name="assigned_admin_id">
                    <option value="">Unassigned</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected($conversation->assigned_admin_id===$admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline-primary w-100">Update conversation</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @include('live-support.partials.realtime-chat', [
        'channelName' => 'live-support-token.'.$conversation->access_token,
        'channelType' => 'public',
    ])
@endpush
