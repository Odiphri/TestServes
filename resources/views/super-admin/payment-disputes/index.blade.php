@extends('super-admin.layout')

@section('title', 'Payment Disputes')
@section('subtitle', 'Investigate contested manual payments, wrong references, duplicate transfers, and finance complaints.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-3"><label class="form-label">Search</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Reference or subject"></div>
        <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option>@foreach(['open','investigating','resolved','rejected','closed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Priority</label><select class="form-select" name="priority"><option value="">All</option>@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected(request('priority')===$priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">School</label><select class="form-select" name="school_id"><option value="">All schools</option>@foreach($schools as $school)<option value="{{ $school->id }}" @selected(request('school_id')==$school->id)>{{ $school->name }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary">Filter</button><a class="btn btn-primary" href="{{ route('super-admin.payment-disputes.create') }}">Open dispute</a></div>
    </form>
</div>

<div class="platform-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Dispute</th><th>School</th><th>Amount</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($disputes as $dispute)
                    <tr>
                        <td><strong>{{ $dispute->reference }}</strong><div>{{ $dispute->subject }}</div><div class="small text-muted">{{ $dispute->created_at->format('M j, Y') }}</div></td>
                        <td>{{ $dispute->school?->name ?? 'No school' }}<div class="small text-muted">{{ $dispute->owner?->name }}</div></td>
                        <td>{{ $dispute->disputed_amount ? 'NGN '.number_format($dispute->disputed_amount, 2) : 'Not set' }}</td>
                        <td><span class="status-badge status-{{ $dispute->status }}">{{ ucfirst($dispute->status) }}</span></td>
                        <td><span class="status-badge status-{{ $dispute->priority }}">{{ ucfirst($dispute->priority) }}</span></td>
                        <td>{{ $dispute->assignedAdmin?->name ?? 'Unassigned' }}</td>
                        <td class="actions-row"><a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.payment-disputes.show', $dispute) }}">View</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.payment-disputes.edit', $dispute) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No payment disputes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $disputes->links() }}
</div>
@endsection
