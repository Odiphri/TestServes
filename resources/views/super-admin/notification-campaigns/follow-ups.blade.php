@extends('super-admin.layout')

@section('title', 'Notification Follow Up')
@section('subtitle', $campaign->title)

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-8">
            <label class="form-label">Search replies</label>
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Owner name or email">
        </div>
        <div class="col-md-4">
            <button class="btn btn-outline-primary">Search</button>
            <a class="btn btn-outline-secondary" href="{{ route('super-admin.notification-campaigns.show', $campaign) }}">Back</a>
        </div>
    </form>
</div>

<div class="platform-card p-3 table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Owner</th><th>School</th><th>Replies</th><th>Last reply</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($recipients as $recipient)
            <tr>
                <td><strong>{{ $recipient->notifiable?->name ?? 'Owner' }}</strong><div class="small text-muted">{{ $recipient->notifiable?->email }}</div></td>
                <td>{{ $recipient->notifiable?->school?->name ?? 'Not set' }}</td>
                <td>{{ $recipient->thread?->messages?->count() ?? 0 }}</td>
                <td>{{ optional($recipient->thread?->messages?->last()?->created_at)->format('M j, Y g:i A') ?? '-' }}</td>
                <td><a class="btn btn-sm btn-primary" href="{{ route('super-admin.notification-campaigns.thread', $recipient) }}">Follow up</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No replies yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $recipients->links() }}
</div>
@endsection
