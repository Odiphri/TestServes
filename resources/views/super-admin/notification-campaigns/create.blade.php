@extends('super-admin.layout')

@section('title', 'Send Notification')
@section('subtitle', 'Send a message to school owners and optionally allow replies.')

@section('content')
<form class="platform-card p-3" method="POST" action="{{ route('super-admin.notification-campaigns.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" maxlength="160" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" required>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', 'information') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Message</label>
            <textarea class="form-control @error('body') is-invalid @enderror" name="body" rows="6" required>{{ old('body') }}</textarea>
            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Recipients</label>
            <select class="form-select @error('recipient_scope') is-invalid @enderror" name="recipient_scope" id="recipientScope" required>
                <option value="single_school_owner" @selected(old('recipient_scope') === 'single_school_owner')>One school owner</option>
                <option value="selected_school_owners" @selected(old('recipient_scope') === 'selected_school_owners')>Selected school owners</option>
                <option value="school_owners_for_school" @selected(old('recipient_scope') === 'school_owners_for_school')>Owners for one school</option>
                @if($canSendPlatformWide)
                    <option value="all_school_owners" @selected(old('recipient_scope') === 'all_school_owners')>All school owners</option>
                @endif
            </select>
            @error('recipient_scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8 recipient-field" data-scope="single_school_owner">
            <label class="form-label">School owner</label>
            <select class="form-select" name="school_owner_id">
                <option value="">Choose owner</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected(old('school_owner_id') == $owner->id)>{{ $owner->name }} - {{ $owner->email }}{{ $owner->school ? ' - '.$owner->school->name : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8 recipient-field d-none" data-scope="selected_school_owners">
            <label class="form-label">Selected owners</label>
            <input class="form-control mb-2" type="search" data-owner-search placeholder="Search owner, email, or school">
            <div class="border rounded p-2" style="max-height: 260px; overflow:auto;">
                @foreach($owners as $owner)
                    @php($haystack = strtolower($owner->name.' '.$owner->email.' '.($owner->school?->name ?? '')))
                    <label class="form-check py-1 owner-choice" data-owner-search-text="{{ $haystack }}">
                        <input class="form-check-input" type="checkbox" name="school_owner_ids[]" value="{{ $owner->id }}" @checked(in_array($owner->id, old('school_owner_ids', [])))>
                        <span class="form-check-label">{{ $owner->name }} - {{ $owner->email }}{{ $owner->school ? ' - '.$owner->school->name : '' }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="col-md-8 recipient-field d-none" data-scope="school_owners_for_school">
            <label class="form-label">School</label>
            <select class="form-select" name="school_id">
                <option value="">Choose school</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}{{ $school->owner ? ' - '.$school->owner->email : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Action URL</label>
            <input class="form-control @error('action_url') is-invalid @enderror" type="url" name="action_url" value="{{ old('action_url') }}" placeholder="https://...">
            @error('action_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Expires at</label>
            <input class="form-control @error('expires_at') is-invalid @enderror" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">
            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Schedule for</label>
            <input class="form-control @error('scheduled_at') is-invalid @enderror" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
            @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="allows_replies" value="1" @checked(old('allows_replies', true))>
                <span class="form-check-label">Allow recipients to reply</span>
            </label>
        </div>
        @if($canSendSystem)
            <div class="col-md-6">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_system_notification" value="1" @checked(old('is_system_notification'))>
                    <span class="form-check-label">System notification, read-only</span>
                </label>
            </div>
        @endif
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary">Send notification</button>
        <a class="btn btn-outline-secondary" href="{{ route('super-admin.notification-campaigns.index') }}">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function syncRecipientFields() {
        const scope = document.getElementById('recipientScope')?.value;
        document.querySelectorAll('.recipient-field').forEach((field) => {
            field.classList.toggle('d-none', field.dataset.scope !== scope);
        });
    }
    document.getElementById('recipientScope')?.addEventListener('change', syncRecipientFields);
    syncRecipientFields();

    document.querySelector('[data-owner-search]')?.addEventListener('input', (event) => {
        const term = event.target.value.toLowerCase().trim();
        document.querySelectorAll('.owner-choice').forEach((choice) => {
            choice.style.display = !term || choice.dataset.ownerSearchText.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
