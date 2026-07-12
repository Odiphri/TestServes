@extends('super-admin.layout')

@section('title', 'Edit Notification')
@section('subtitle', 'Update campaign text and behavior without re-sending it.')

@section('content')
<form class="platform-card p-3" method="POST" action="{{ route('super-admin.notification-campaigns.update', $campaign) }}">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $campaign->title) }}" maxlength="160" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <input class="form-control" name="type" value="{{ old('type', $campaign->type) }}" placeholder="general">
        </div>
        <div class="col-12">
            <label class="form-label">Message</label>
            <textarea class="form-control @error('body') is-invalid @enderror" name="body" rows="6" required>{{ old('body', $campaign->body) }}</textarea>
            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">Action URL</label>
            <input class="form-control @error('action_url') is-invalid @enderror" type="url" name="action_url" value="{{ old('action_url', $campaign->action_url) }}" placeholder="https://...">
            @error('action_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Expires at</label>
            <input class="form-control @error('expires_at') is-invalid @enderror" type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($campaign->expires_at)->format('Y-m-d\\TH:i')) }}">
            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="allows_replies" value="1" @checked(old('allows_replies', $campaign->allows_replies))>
                <span class="form-check-label">Allow recipients to reply</span>
            </label>
        </div>
        @if($canSendSystem)
            <div class="col-md-6">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_system_notification" value="1" @checked(old('is_system_notification', $campaign->is_system_notification))>
                    <span class="form-check-label">System notification, read-only</span>
                </label>
            </div>
        @endif
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary">Save changes</button>
        <a class="btn btn-outline-secondary" href="{{ route('super-admin.notification-campaigns.show', $campaign) }}">Cancel</a>
    </div>
</form>
@endsection
