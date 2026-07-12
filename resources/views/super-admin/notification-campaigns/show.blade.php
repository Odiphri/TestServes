@extends('super-admin.layout')

@section('title', 'Notification Details')
@section('subtitle', 'Inspect recipients, delivery state, and replies.')

@section('content')
<div class="platform-card p-3 mb-3">
    <div class="d-flex justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="h5 mb-1">{{ $campaign->title }}</h2>
            <p class="mb-2">{{ $campaign->body }}</p>
            <div class="text-muted small">
                Type: {{ $campaign->type }} |
                Scope: {{ str_replace('_', ' ', $campaign->recipient_scope) }} |
                Created by: {{ $campaign->creator?->name ?? 'System' }}
            </div>
        </div>
        <div class="text-end">
            <span class="status-badge status-{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span>
            <div class="small text-muted mt-2">{{ optional($campaign->sent_at)->format('M j, Y g:i A') }}</div>
            <div class="actions-row justify-content-end mt-3">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.notification-campaigns.edit', $campaign) }}">Edit</a>
                <form method="POST" action="{{ route('super-admin.notification-campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this notification campaign?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="platform-card p-3 table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Recipient</th>
                <th>School</th>
                <th>Delivered</th>
                <th>Read</th>
                <th>Replies</th>
            </tr>
        </thead>
        <tbody>
        @forelse($campaign->recipients as $recipient)
            <tr>
                <td>
                    <strong>{{ $recipient->notifiable?->name ?? $recipient->notifiable?->full_name ?? 'Recipient' }}</strong>
                    <div class="small text-muted">{{ $recipient->notifiable?->email }}</div>
                </td>
                <td>{{ $recipient->notifiable?->school?->name ?? $campaign->school?->name ?? 'Not set' }}</td>
                <td>{{ optional($recipient->delivered_at)->format('M j, Y g:i A') ?? 'No' }}</td>
                <td>{{ optional($recipient->read_at)->format('M j, Y g:i A') ?? 'Unread' }}</td>
                <td>
                    @if($recipient->thread?->messages?->isNotEmpty())
                        @foreach($recipient->thread->messages as $message)
                            <div class="mb-2">
                                <div>{{ $message->message }}</div>
                                <div class="small text-muted">{{ optional($message->created_at)->format('M j, Y g:i A') }}</div>
                            </div>
                        @endforeach
                    @else
                        <span class="text-muted small">No replies</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No recipients found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
