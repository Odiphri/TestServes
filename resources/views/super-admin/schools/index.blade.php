@extends('super-admin.layout')

@section('title', 'Schools')
@section('subtitle', 'Manage registered schools, owners, plans, and subscription status.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="School, slug, owner, or email">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                @foreach(['pending', 'active', 'suspended', 'trial', 'expired', 'deactivated'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Plan</label>
            <select class="form-select" name="plan">
                <option value="">All plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(request('plan') == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-center">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="archived" value="1" id="archived" @checked(request()->boolean('archived'))>
                <label class="form-check-label" for="archived">Archived</label>
            </div>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-outline-primary" type="submit">Filter</button>
            @if($canManage)
                <a class="btn btn-primary" href="{{ route('super-admin.schools.create') }}">Create school</a>
            @endif
        </div>
    </form>
</div>

<div class="platform-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>School</th>
                    <th>Owner</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Subscription</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td>
                            <strong>{{ $school->name }}</strong>
                            <div class="small text-muted">{{ $school->slug }}</div>
                            <a class="small" href="{{ $school->portal_url }}" target="_blank" rel="noopener">Portal link</a>
                        </td>
                        <td>
                            {{ $school->owner?->name ?? 'Not assigned' }}
                            <div class="small text-muted">{{ $school->owner?->email }}</div>
                            <div class="small text-muted">{{ $school->owner?->phone }}</div>
                        </td>
                        <td>{{ $school->plan?->name ?? 'No plan' }}</td>
                        <td><span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span></td>
                        <td>
                            <div>{{ optional($school->subscription_starts_at)->format('M j, Y') ?? 'Not set' }}</div>
                            <div class="small text-muted">Expires: {{ optional($school->subscription_expires_at)->format('M j, Y') ?? 'Not set' }}</div>
                            <div class="small text-muted">Due: {{ optional($school->next_payment_due_at)->format('M j, Y') ?? 'Not set' }}</div>
                            <div class="small text-muted">Deactivate: {{ optional($school->deactivation_scheduled_at)->format('M j, Y') ?? 'Not scheduled' }}</div>
                        </td>
                        <td>{{ $school->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="actions-row">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.schools.show', $school) }}">View</a>
                                @if($school->trashed())
                                    @if($canManage)
                                        <form action="{{ route('super-admin.schools.restore', $school->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Restore</button>
                                        </form>
                                    @endif
                                @elseif($canManage)
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.schools.edit', $school) }}">Edit</a>
                                    @foreach(['active' => 'Activate', 'suspended' => 'Suspend', 'trial' => 'Trial', 'expired' => $school->status === 'trial' ? 'End trial' : 'Expire', 'deactivated' => 'Deactivate'] as $status => $label)
                                        @continue($school->status === $status)
                                        <form action="{{ route('super-admin.schools.status', [$school, $status]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-dark" type="submit">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                    <form action="{{ route('super-admin.schools.destroy', $school) }}" method="POST" onsubmit="return confirm('Delete this school? It will move to Archived schools.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No schools found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $schools->links() }}
</div>
@endsection
