@extends('super-admin.layout')

@section('title', 'Live Support Chat')
@section('subtitle', $conversation->reference)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="platform-card p-3 mb-3">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $conversation->subject ?? 'Live support conversation' }}</h2>
                    <div class="text-muted">{{ $conversation->visitor_name ?? 'Visitor' }} &middot; {{ $conversation->visitor_email ?? 'No email' }}</div>
                </div>
                <span class="status-badge status-{{ $conversation->status }} align-self-start">{{ ucfirst($conversation->status) }}</span>
            </div>
        </div>

        <div class="platform-card p-3 mb-3">
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

        @if($conversation->status !== 'closed')
            <form class="platform-card p-3" method="POST" action="{{ route('super-admin.live-support.reply', $conversation) }}" data-live-support-form>
                @csrf
                <label class="form-label">Reply</label>
                <textarea class="form-control mb-3" name="message" rows="4" required></textarea>
                <button class="btn btn-primary">Send reply</button>
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
