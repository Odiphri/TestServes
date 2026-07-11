<div class="platform-card p-3">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Plan name</label>
            <input class="form-control" name="name" value="{{ old('name', $plan->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug', $plan->slug) }}" placeholder="starter">
        </div>
        <div class="col-md-3">
            <label class="form-label">Monthly price</label>
            <input class="form-control" type="number" step="0.01" min="0" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price ?? 0) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Yearly price</label>
            <input class="form-control" type="number" step="0.01" min="0" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price ?? 0) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Trial days</label>
            <input class="form-control" type="number" min="0" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 0) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Admin accounts</label>
            <input class="form-control" type="number" min="1" max="50" name="admin_limit" value="{{ old('admin_limit', $plan->admin_limit ?? 1) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" required>
                @foreach(['active', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $plan->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <input type="hidden" name="is_recommended" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_recommended" value="1" id="is_recommended" @checked(old('is_recommended', $plan->is_recommended))>
                <label class="form-check-label" for="is_recommended">Recommended plan</label>
            </div>
        </div>
        <div class="col-12">
            @php
                $selectedFeatures = old('features', $plan->features ?? []);
            @endphp
            <label class="form-label">Included app features</label>
            <div class="row g-2">
                @foreach($availableFeatures as $feature)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded p-2 h-100">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="features[]"
                                    value="{{ $feature }}"
                                    id="feature_{{ $loop->index }}"
                                    @checked(in_array($feature, $selectedFeatures, true))
                                >
                                <label class="form-check-label" for="feature_{{ $loop->index }}">{{ $feature }}</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('features')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $button }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('super-admin.subscription-plans.index') }}">Cancel</a>
</div>
