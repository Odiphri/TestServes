@php
    $owner = $school->owner;
    $branding = $school->branding;
@endphp
<div class="row g-3">
    <div class="col-lg-8">
        <div class="platform-card p-3 mb-3">
            <h2 class="h5 mb-3">School Details</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School name</label>
                    <input class="form-control" name="name" value="{{ old('name', $school->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">School slug</label>
                    <input class="form-control" name="slug" value="{{ old('slug', $school->slug) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Plan</label>
                    <select class="form-select" name="subscription_plan_id">
                        <option value="">No plan yet</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('subscription_plan_id', $school->subscription_plan_id) == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        @foreach(['pending', 'active', 'suspended', 'trial', 'expired'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $school->status ?: 'pending') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subscription start date</label>
                    <input class="form-control" type="date" name="subscription_starts_at" value="{{ old('subscription_starts_at', optional($school->subscription_starts_at)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subscription expiry date</label>
                    <input class="form-control" type="date" name="subscription_expires_at" value="{{ old('subscription_expires_at', optional($school->subscription_expires_at)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact email</label>
                    <input class="form-control" type="email" name="contact_email" value="{{ old('contact_email', $school->contact_email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact phone</label>
                    <input class="form-control" name="contact_phone" value="{{ old('contact_phone', $school->contact_phone) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="2">{{ old('address', $school->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">School type</label>
                    <input class="form-control" name="school_type" value="{{ old('school_type', $school->school_type) }}" placeholder="Nursery, Primary, Secondary">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expected students</label>
                    <input class="form-control" type="number" min="0" name="expected_students_count" value="{{ old('expected_students_count', $school->expected_students_count) }}">
                </div>
            </div>
        </div>

        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Owner Details</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Owner name</label>
                    <input class="form-control" name="owner_name" value="{{ old('owner_name', $owner?->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Owner email</label>
                    <input class="form-control" type="email" name="owner_email" value="{{ old('owner_email', $owner?->email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Owner phone</label>
                    <input class="form-control" name="owner_phone" value="{{ old('owner_phone', $owner?->phone) }}">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="platform-card p-3">
            <h2 class="h5 mb-3">Branding Settings</h2>
            <div class="mb-3">
                <label class="form-label">Portal display name</label>
                <input class="form-control" name="portal_display_name" value="{{ old('portal_display_name', $branding?->portal_display_name) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">School short name</label>
                <input class="form-control" name="short_name" value="{{ old('short_name', $branding?->short_name) }}">
            </div>
            <div class="row g-3">
                <div class="col-4">
                    <label class="form-label">Primary</label>
                    <input class="form-control form-control-color w-100" type="color" name="primary_color" value="{{ old('primary_color', $branding?->primary_color ?? '#2563eb') }}">
                </div>
                <div class="col-4">
                    <label class="form-label">Secondary</label>
                    <input class="form-control form-control-color w-100" type="color" name="secondary_color" value="{{ old('secondary_color', $branding?->secondary_color ?? '#0f172a') }}">
                </div>
                <div class="col-4">
                    <label class="form-label">Accent</label>
                    <input class="form-control form-control-color w-100" type="color" name="accent_color" value="{{ old('accent_color', $branding?->accent_color ?? '#22c55e') }}">
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Logo</label>
                <input class="form-control" type="file" name="logo" accept="image/*">
                @if($branding?->logo_path)
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <img src="{{ $branding->logo_url }}" alt="{{ $school->name }} logo" style="width:42px;height:42px;object-fit:contain;" onerror="this.src='{{ \App\Models\SystemSetting::platformLogoUrl() }}'">
                        <span class="small text-muted">Current logo</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $button }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('super-admin.schools.index') }}">Cancel</a>
</div>
