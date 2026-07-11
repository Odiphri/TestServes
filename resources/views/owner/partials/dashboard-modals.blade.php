<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" action="{{ route('platform.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-header"><h2 class="modal-title h5">Edit profile</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="name" value="{{ old('name', $owner->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $owner->email) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $owner->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label">Profile picture</label><input class="form-control" type="file" name="profile_picture" accept="image/*"></div>
                    @if($owner->profile_picture)<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="remove_profile_picture" value="1"> Remove current picture</label></div>@endif
                    <div class="col-12"><hr><strong>Change password</strong><p class="text-muted small mb-0">Leave empty if you do not want to change it.</p></div>
                    <div class="col-md-4"><label class="form-label">Current password</label><input class="form-control" type="password" name="current_password" autocomplete="current-password"></div>
                    <div class="col-md-4"><label class="form-label">New password</label><input class="form-control" type="password" name="new_password" autocomplete="new-password"></div>
                    <div class="col-md-4"><label class="form-label">Confirm new password</label><input class="form-control" type="password" name="new_password_confirmation" autocomplete="new-password"></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save profile</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="schoolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" action="{{ route('platform.school.update') }}" method="POST">
            @csrf
            <div class="modal-header"><h2 class="modal-title h5">Edit school</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">School name</label><input class="form-control" name="school_name" value="{{ old('school_name', $school?->name) }}"></div>
                    <div class="col-md-6"><label class="form-label">Portal name</label><input class="form-control" name="school_slug" value="{{ old('school_slug', $school?->slug) }}"></div>
                    <div class="col-md-6"><label class="form-label">School type</label><select class="form-select" name="school_type"><option value="">Choose later</option>@foreach(['Nursery', 'Primary', 'Secondary', 'Combined'] as $type)<option value="{{ $type }}" @selected(old('school_type', $school?->school_type) === $type)>{{ $type }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Expected students</label><input class="form-control" type="number" min="1" name="expected_students_count" value="{{ old('expected_students_count', $school?->expected_students_count) }}"></div>
                    <div class="col-md-6"><label class="form-label">Contact email</label><input class="form-control" type="email" name="contact_email" value="{{ old('contact_email', $school?->contact_email ?: $owner->email) }}"></div>
                    <div class="col-md-6"><label class="form-label">Contact phone</label><input class="form-control" name="contact_phone" value="{{ old('contact_phone', $school?->contact_phone ?: $owner->phone) }}"></div>
                    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="school_address" rows="3">{{ old('school_address', $school?->address) }}</textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save school</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="brandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" action="{{ route('platform.branding.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header"><h2 class="modal-title h5">Edit branding</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Display name</label><input class="form-control" name="portal_display_name" value="{{ old('portal_display_name', $branding?->portal_display_name) }}"></div>
                    <div class="col-md-6"><label class="form-label">Short name</label><input class="form-control" name="short_name" value="{{ old('short_name', $branding?->short_name) }}"></div>
                    <div class="col-md-4"><label class="form-label">Primary</label><input class="form-control form-control-color w-100" type="color" name="primary_color" value="{{ old('primary_color', $branding?->primary_color ?? '#2563eb') }}"></div>
                    <div class="col-md-4"><label class="form-label">Secondary</label><input class="form-control form-control-color w-100" type="color" name="secondary_color" value="{{ old('secondary_color', $branding?->secondary_color ?? '#0f172a') }}"></div>
                    <div class="col-md-4"><label class="form-label">Accent</label><input class="form-control form-control-color w-100" type="color" name="accent_color" value="{{ old('accent_color', $branding?->accent_color ?? '#22c55e') }}"></div>
                    <div class="col-12">
                        <label class="form-label">Logo</label>
                        @if($branding?->logo_path)
                            <div class="school-logo-preview mb-2">
                                <img src="{{ $branding->logo_url }}" alt="{{ $school?->name }} logo" onerror="this.src='{{ \App\Models\SystemSetting::platformLogoUrl() }}'">
                            </div>
                        @endif
                        <input class="form-control" type="file" name="logo" accept="image/*">
                    </div>
                    @if($branding?->logo_path)<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="remove_logo" value="1"> Remove current logo</label></div>@endif
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save branding</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form class="modal-content" action="{{ route('platform.plan.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header"><h2 class="modal-title h5">Choose plan</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="pricing-grid">
                    <label class="pricing-card {{ blank(old('subscription_plan_id', $school?->subscription_plan_id)) ? 'selected' : '' }}">
                        <input type="radio" name="subscription_plan_id" value="" @checked(blank(old('subscription_plan_id', $school?->subscription_plan_id)))>
                        <span class="pricing-top"><strong>Choose later</strong><em>No pressure</em></span>
                        <span class="pricing-price">NGN 0</span>
                        <span class="pricing-sub">Keep your workspace open without a plan.</span>
                    </label>
                    @foreach($plans as $plan)
                        @php
                            $monthly = (float) $plan->monthly_price;
                            $yearly = (float) $plan->yearly_price;
                            $annualFull = $monthly * 12;
                            $discount = $annualFull > 0 && $yearly > 0 && $yearly < $annualFull ? round((($annualFull - $yearly) / $annualFull) * 100) : 0;
                        @endphp
                        <label class="pricing-card {{ (int) old('subscription_plan_id', $school?->subscription_plan_id) === $plan->id ? 'selected' : '' }}">
                            <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" @checked((int) old('subscription_plan_id', $school?->subscription_plan_id) === $plan->id)>
                            <span class="pricing-top"><strong>{{ $plan->name }}</strong>@if($plan->is_recommended)<em>Recommended</em>@elseif($discount > 0)<em>{{ $discount }}% yearly off</em>@endif</span>
                            <span class="pricing-price">NGN {{ number_format($monthly, 0) }}<small>/month</small></span>
                            <span class="pricing-sub">NGN {{ number_format($yearly, 0) }} yearly @if($discount > 0) · save {{ $discount }}%@endif</span>
                            <span class="pricing-trial">{{ $plan->trial_days }} trial days</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save plan</button></div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.pricing-card input').forEach((input) => {
    input.addEventListener('change', () => {
        const grid = input.closest('.pricing-grid');
        grid?.querySelectorAll('.pricing-card').forEach((card) => card.classList.remove('selected'));
        input.closest('.pricing-card')?.classList.add('selected');
    });
});
</script>
