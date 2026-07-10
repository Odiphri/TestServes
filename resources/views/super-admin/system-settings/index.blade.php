@extends('super-admin.layout')

@section('title', 'Platform Configuration')
@section('subtitle', 'Control TestServes-wide business, payment, subscription, branding, and maintenance settings.')

@section('content')
@php
    $requestedSection = request('section');
    $activeSection = array_key_exists($requestedSection, $sections) ? $requestedSection : array_key_first($sections);
    $secretFilled = fn ($key) => filled($settings[$key] ?? null);
    $savedPlatformLogo = $settings['platform_logo'] ?? null;
    $savedPlatformLogoUrl = \App\Support\PublicDiskUrl::make($savedPlatformLogo);
    $platformLogoPreview = $savedPlatformLogoUrl ?? \App\Models\SystemSetting::platformLogoUrl();
@endphp

@push('styles')
    <style>
        .platform-upload-preview {
            min-width: 154px;
        }
        .platform-upload-fallback {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #f8fafc;
            color: #94a3b8;
            display: none;
            place-items: center;
            font-weight: 800;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

<div class="row g-3">
    <div class="col-lg-3">
        <div class="platform-card p-3 position-sticky" style="top: 16px;">
            <div class="small text-muted mb-2">Configuration areas</div>
            <div class="nav flex-column nav-pills gap-1" id="settingsTabs" role="tablist" aria-orientation="vertical">
                @foreach($sections as $key => $section)
                    <button
                        class="nav-link text-start {{ $activeSection === $key ? 'active' : '' }}"
                        id="settings-{{ $key }}-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#settings-{{ $key }}"
                        type="button"
                        role="tab"
                        aria-controls="settings-{{ $key }}"
                        aria-selected="{{ $activeSection === $key ? 'true' : 'false' }}"
                    >
                        {{ $section['title'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <form method="POST" action="{{ route('super-admin.system-settings.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="platform-card p-3 mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">Platform settings, not school creation</h2>
                        <p class="text-muted mb-0">
                            This page only saves global TestServes configuration. It does not create schools or change the CBT portal.
                        </p>
                    </div>
                    <button class="btn btn-primary align-self-start">Save configuration</button>
                </div>
            </div>

            <div class="tab-content" id="settingsTabsContent">
                @foreach($sections as $key => $section)
                    <section
                        class="tab-pane fade {{ $activeSection === $key ? 'show active' : '' }}"
                        id="settings-{{ $key }}"
                        role="tabpanel"
                        aria-labelledby="settings-{{ $key }}-tab"
                        tabindex="0"
                    >
                        <div class="platform-card p-3 mb-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                <div>
                                    <h2 class="h4 mb-1">{{ $section['title'] }}</h2>
                                    <p class="text-muted mb-0">{{ $section['description'] }}</p>
                                </div>

                                @if($key === 'paystack')
                                    <span class="status-badge status-{{ ($settings['paystack_enabled'] ?? '') === '1' ? 'active' : 'pending' }} align-self-start">
                                        {{ ($settings['paystack_enabled'] ?? '') === '1' ? 'Credentials saved' : 'Not enabled' }}
                                    </span>
                                @endif
                            </div>

                            @if($key === 'paystack')
                                <div class="alert alert-warning mb-3">
                                    Paystack automation is not connected yet. When you get your Paystack keys, paste them here so the platform is ready for the payment integration phase.
                                </div>
                            @endif

                            <div class="row g-3">
                                @foreach($section['fields'] as $fieldKey => $field)
                                    <div class="{{ in_array($field['type'], ['textarea'], true) ? 'col-12' : 'col-md-6' }}">
                                        @if($field['type'] === 'boolean')
                                            <input type="hidden" name="{{ $fieldKey }}" value="0">
                                            <div class="border rounded p-3 h-100">
                                                <div class="form-check form-switch">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="{{ $fieldKey }}"
                                                        value="1"
                                                        id="{{ $fieldKey }}"
                                                        @checked(old($fieldKey, $settings[$fieldKey] ?? '') === '1')
                                                    >
                                                    <label class="form-check-label fw-semibold" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                                </div>
                                            </div>
                                        @elseif($field['type'] === 'file')
                                            <label class="form-label" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                            @if($fieldKey === 'platform_logo' && $platformLogoPreview)
                                                <div class="platform-upload-preview mb-2 border rounded p-2 d-inline-flex align-items-center gap-2">
                                                    <img src="{{ $platformLogoPreview }}" alt="{{ $field['label'] }}" style="width:42px;height:42px;object-fit:contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='grid';this.parentElement.querySelector('[data-preview-note]').textContent='Logo preview failed to load. Run php artisan storage:link and check file permissions.';">
                                                    <span class="platform-upload-fallback">TS</span>
                                                    <span class="small text-muted" data-preview-note>
                                                        {{ filled($savedPlatformLogo) && ! $savedPlatformLogoUrl ? 'Saved upload is missing; showing default logo.' : 'Current upload' }}
                                                    </span>
                                                </div>
                                            @endif
                                            <input
                                                class="form-control @error($fieldKey) is-invalid @enderror"
                                                type="file"
                                                id="{{ $fieldKey }}"
                                                name="{{ $fieldKey }}"
                                                accept="image/*"
                                            >
                                            <div class="form-text">Upload JPG, PNG, WebP, or SVG up to 5MB. TestServes will use it as the platform logo/app icon where the platform chrome supports it.</div>
                                            @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @elseif($field['type'] === 'textarea')
                                            <label class="form-label" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                            <textarea
                                                class="form-control @error($fieldKey) is-invalid @enderror"
                                                rows="4"
                                                id="{{ $fieldKey }}"
                                                name="{{ $fieldKey }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                            >{{ old($fieldKey, $settings[$fieldKey] ?? '') }}</textarea>
                                            @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @elseif($field['type'] === 'select')
                                            <label class="form-label" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                            <select class="form-select @error($fieldKey) is-invalid @enderror" id="{{ $fieldKey }}" name="{{ $fieldKey }}">
                                                <option value="">Choose option</option>
                                                @foreach($field['options'] as $value => $label)
                                                    <option value="{{ $value }}" @selected(old($fieldKey, $settings[$fieldKey] ?? '') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @elseif($field['type'] === 'secret')
                                            <label class="form-label" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                            <input
                                                class="form-control @error($fieldKey) is-invalid @enderror"
                                                type="password"
                                                id="{{ $fieldKey }}"
                                                name="{{ $fieldKey }}"
                                                value=""
                                                placeholder="{{ $secretFilled($fieldKey) ? 'Secret already saved - leave blank to keep it' : ($field['placeholder'] ?? '') }}"
                                                autocomplete="new-password"
                                            >
                                            <div class="form-text">
                                                {{ $secretFilled($fieldKey) ? 'A value is already saved. Enter a new value only if you want to replace it.' : 'No value saved yet.' }}
                                            </div>
                                            @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @else
                                            <label class="form-label" for="{{ $fieldKey }}">{{ $field['label'] }}</label>
                                            <input
                                                class="form-control {{ $field['type'] === 'color' ? 'form-control-color w-100' : '' }} @error($fieldKey) is-invalid @enderror"
                                                type="{{ $field['type'] }}"
                                                id="{{ $fieldKey }}"
                                                name="{{ $fieldKey }}"
                                                value="{{ old($fieldKey, $settings[$fieldKey] ?? ($field['default'] ?? '')) }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                            >
                                            @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary">Save configuration</button>
            </div>
        </form>
    </div>
</div>
@endsection
