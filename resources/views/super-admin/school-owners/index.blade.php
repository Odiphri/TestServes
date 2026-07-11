@extends('super-admin.layout')

@section('title', 'School Owners')
@section('subtitle', 'Search, inspect, enable, disable, and reset school owner accounts.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-6"><label class="form-label">Search</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Owner, email, phone, or school"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['active','disabled','pending'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-3"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
    </form>
</div>
<div class="platform-card p-3 table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Owner</th><th>School</th><th>Status</th><th>Registered</th><th>Last login</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($owners as $owner)
            <tr>
                <td><strong>{{ $owner->name }}</strong><div class="small text-muted">{{ $owner->email }} {{ $owner->phone }}</div></td>
                <td>{{ $owner->school?->name ?? 'No school' }}</td>
                <td><span class="status-badge status-{{ $owner->status ?? 'active' }}">{{ ucfirst($owner->status ?? 'active') }}</span></td>
                <td>{{ $owner->created_at->format('M j, Y') }}</td>
                <td>{{ optional($owner->last_login_at)->format('M j, Y g:ia') ?? 'Not available' }}</td>
                <td class="actions-row">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.school-owners.show', $owner) }}">View</a>
                    @if(Auth::guard('platform_admin')->user()?->isSuperAdmin())
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('super-admin.school-owners.edit', $owner) }}">Edit</a>
                        @foreach(['active' => 'Enable', 'disabled' => 'Disable'] as $status => $label)
                            <form method="POST" action="{{ route('super-admin.school-owners.status', [$owner, $status]) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-dark">{{ $label }}</button></form>
                        @endforeach
                        <form method="POST" action="{{ route('super-admin.school-owners.destroy', $owner) }}" onsubmit="return confirm('Delete this school owner account? This does not delete the school.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">No school owners found.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $owners->links() }}
</div>
@endsection
