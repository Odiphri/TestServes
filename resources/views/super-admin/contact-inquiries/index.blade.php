@extends('super-admin.layout')

@section('title', 'Contact Inquiries')
@section('subtitle', 'Review public contact form messages and route them to support.')

@section('content')
<div class="platform-card p-3 mb-3">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-4">
            <label class="form-label" for="search">Search</label>
            <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, email, subject">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">All statuses</option>
                @foreach(\App\Models\ContactInquiry::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="category">Category</label>
            <select class="form-select" id="category" name="category">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
</div>

<div class="platform-card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Category</th>
                <th>From</th>
                <th>Status</th>
                <th>Assigned</th>
                <th>Submitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($inquiries as $inquiry)
                <tr>
                    <td><strong>{{ $inquiry->subject }}</strong><br><span class="text-muted small">{{ $inquiry->school_name ?: 'No school name' }}</span></td>
                    <td>{{ $inquiry->category }}</td>
                    <td>{{ $inquiry->name }}<br><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                    <td><span class="status-badge status-{{ $inquiry->status }}">{{ ucwords(str_replace('_', ' ', $inquiry->status)) }}</span></td>
                    <td>{{ $inquiry->assignedAdmin?->name ?? 'Unassigned' }}</td>
                    <td>{{ optional($inquiry->submitted_at)->diffForHumans() }}</td>
                    <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('super-admin.contact-inquiries.show', $inquiry) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-muted p-4">No contact inquiries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $inquiries->links() }}</div>
@endsection
