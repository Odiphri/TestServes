@extends('super-admin.layout')

@section('title', 'Contact Inquiry')
@section('subtitle', $inquiry->subject)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="platform-card p-3 mb-3">
            <div class="d-flex justify-content-between gap-3 align-items-start">
                <div>
                    <h2 class="h4 mb-1">{{ $inquiry->subject }}</h2>
                    <p class="text-muted mb-0">{{ $inquiry->category }} · {{ optional($inquiry->submitted_at)->format('M j, Y g:ia') }}</p>
                </div>
                <span class="status-badge status-{{ $inquiry->status }}">{{ ucwords(str_replace('_', ' ', $inquiry->status)) }}</span>
            </div>
            <hr>
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $inquiry->name }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd>
                <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $inquiry->phone ?: 'Not provided' }}</dd>
                <dt class="col-sm-3">School</dt><dd class="col-sm-9">{{ $inquiry->school_name ?: 'Not provided' }}</dd>
                <dt class="col-sm-3">Message</dt><dd class="col-sm-9" style="white-space: pre-wrap;">{{ $inquiry->message }}</dd>
            </dl>
        </div>

        <div class="platform-card p-3 mb-3">
            <h3 class="h5">Send response</h3>
            <form method="POST" action="{{ route('super-admin.contact-inquiries.respond', $inquiry) }}">
                @csrf
                <textarea class="form-control mb-2 @error('response') is-invalid @enderror" name="response" rows="6" required>{{ old('response') }}</textarea>
                @error('response')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <button class="btn btn-primary">Send response</button>
            </form>
        </div>

        <div class="platform-card p-3">
            <h3 class="h5">Internal note</h3>
            <form method="POST" action="{{ route('super-admin.contact-inquiries.notes', $inquiry) }}">
                @csrf
                <textarea class="form-control mb-2 @error('note') is-invalid @enderror" name="note" rows="4" required>{{ old('note') }}</textarea>
                @error('note')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <button class="btn btn-outline-secondary">Add note to audit log</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="platform-card p-3 mb-3">
            <h3 class="h5">Assignment</h3>
            <form method="POST" action="{{ route('super-admin.contact-inquiries.assign', $inquiry) }}">
                @csrf
                @method('PATCH')
                <select class="form-select mb-2" name="assigned_admin_id">
                    <option value="">Unassigned</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected($inquiry->assigned_admin_id === $admin->id)>{{ $admin->name }} · {{ $admin->roleLabel() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary w-100">Save assignment</button>
            </form>
        </div>

        <div class="platform-card p-3">
            <h3 class="h5">Status</h3>
            <form method="POST" action="{{ route('super-admin.contact-inquiries.status', $inquiry) }}">
                @csrf
                @method('PATCH')
                <select class="form-select mb-2" name="status">
                    @foreach(\App\Models\ContactInquiry::STATUSES as $status)
                        <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary w-100">Update status</button>
            </form>
        </div>
    </div>
</div>
@endsection
