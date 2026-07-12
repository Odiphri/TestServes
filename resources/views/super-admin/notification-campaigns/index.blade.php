@extends('super-admin.layout')

@section('title', 'Notifications')
@section('subtitle', 'Create, send, and inspect notification campaigns.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Title, body, or type">
        </div>
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <input class="form-control" name="type" value="{{ request('type') }}" placeholder="general">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">All</option>
                @foreach(['sent', 'draft', 'scheduled', 'failed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-outline-primary">Filter</button>
            <a class="btn btn-primary" href="{{ route('super-admin.notification-campaigns.create') }}">Send notification</a>
        </div>
    </form>
</div>

<div class="platform-card p-3 table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Notification</th>
                <th>Scope</th>
                <th>Recipients</th>
                <th>Status</th>
                <th>Sent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($campaigns as $campaign)
            <tr>
                <td>
                    <strong>{{ $campaign->title }}</strong>
                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($campaign->body, 90) }}</div>
                    <div class="small text-muted">{{ $campaign->type }}</div>
                </td>
                <td>{{ str_replace('_', ' ', $campaign->recipient_scope) }}</td>
                <td>{{ $campaign->recipients_count }}</td>
                <td><span class="status-badge status-{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span></td>
                <td>{{ optional($campaign->sent_at)->format('M j, Y g:i A') ?? 'Not sent' }}</td>
                <td>
                    <div class="actions-row">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.notification-campaigns.show', $campaign) }}">View</a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.notification-campaigns.edit', $campaign) }}">Edit</a>
                        <form method="POST" action="{{ route('super-admin.notification-campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this notification campaign?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">No notification campaigns yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $campaigns->links() }}
</div>
@endsection
