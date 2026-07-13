@extends('super-admin.layout')

@section('title', 'Live Support')
@section('subtitle', 'Direct visitor and school-owner chats, separate from support tickets.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4"><label class="form-label">Search</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Reference, name, email, subject"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option>@foreach(['open','waiting','answered','closed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Priority</label><select class="form-select" name="priority"><option value="">All</option>@foreach(['low','medium','high'] as $priority)<option value="{{ $priority }}" @selected(request('priority')===$priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-outline-primary">Filter</button></div>
    </form>
</div>

<div class="platform-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Conversation</th><th>Visitor</th><th>School</th><th>Status</th><th>Messages</th><th>Assigned</th><th>Action</th></tr></thead>
            <tbody data-live-support-admin-list>
                @forelse($conversations as $conversation)
                    <tr data-conversation-id="{{ $conversation->id }}">
                        <td><strong>{{ $conversation->reference }}</strong><div>{{ $conversation->subject ?? 'No subject' }}</div><div class="small text-muted">{{ optional($conversation->last_message_at ?? $conversation->created_at)->diffForHumans() }}</div></td>
                        <td>{{ $conversation->visitor_name ?? 'Visitor' }}<div class="small text-muted">{{ $conversation->visitor_email }}</div></td>
                        <td>{{ $conversation->school?->name ?? 'Platform visitor' }}</td>
                        <td><span class="status-badge status-{{ $conversation->status }}">{{ ucfirst($conversation->status) }}</span></td>
                        <td data-message-count>{{ $conversation->messages_count }}</td>
                        <td>{{ $conversation->assignedAdmin?->name ?? 'Unassigned' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.live-support.show', $conversation) }}">Open chat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No live support conversations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $conversations->links() }}
</div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.querySelector('[data-live-support-admin-list]');
    if (!list) return;

    const showUrlTemplate = @json(route('super-admin.live-support.show', ['liveSupport' => '__ID__']));
    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

    const rowHtml = (payload) => {
        const conversation = payload.conversation || {};
        const url = showUrlTemplate.replace('__ID__', conversation.id);
        return `
            <td><strong>${escapeHtml(conversation.reference)}</strong><div>${escapeHtml(conversation.subject || 'No subject')}</div><div class="small text-muted">Just now</div></td>
            <td>${escapeHtml(conversation.visitor_name || 'Visitor')}<div class="small text-muted">${escapeHtml(conversation.visitor_email || '')}</div></td>
            <td>${escapeHtml(conversation.school_name || 'Platform visitor')}</td>
            <td><span class="status-badge status-${escapeHtml(conversation.status || 'open')}">${escapeHtml(conversation.status || 'open')}</span></td>
            <td data-message-count>1</td>
            <td>Unassigned</td>
            <td><a class="btn btn-sm btn-outline-primary" href="${url}">Open chat</a></td>
        `;
    };

    const connect = () => {
        if (!window.Echo) {
            setTimeout(connect, 300);
            return;
        }

        window.Echo.channel('live-support-admin').listen('.message.sent', (payload) => {
            const id = payload?.conversation?.id;
            if (!id) return;

            const existing = list.querySelector(`[data-conversation-id="${id}"]`);
            if (existing) {
                const count = existing.querySelector('[data-message-count]');
                if (count) count.textContent = String((Number.parseInt(count.textContent || '0', 10) || 0) + 1);
                list.prepend(existing);
                return;
            }

            const empty = list.querySelector('td[colspan]');
            empty?.closest('tr')?.remove();
            const row = document.createElement('tr');
            row.dataset.conversationId = id;
            row.innerHTML = rowHtml(payload);
            list.prepend(row);
        });
    };

    connect();
})();
</script>
@endpush
