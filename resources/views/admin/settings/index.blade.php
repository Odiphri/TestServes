@extends('layouts.admin')

@section('title', 'School Information')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">School Information</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label for="school_name" class="form-label">School Name</label>
                    <input type="text" class="form-control" id="school_name" name="school_name" value="{{ old('school_name', $settings->school_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label for="logo" class="form-label">School Logo</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" onchange="previewLogo(event)">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Logo</label>
                <div class="d-flex align-items-center gap-3">
                    <img
                        id="logoPreview"
                        src="{{ $settings->logo_path ? asset('storage/' . $settings->logo_path) : asset('images/default-school-icon.svg') }}"
                        alt="School logo"
                        style="height: 88px; width: 88px; object-fit: contain; padding: 6px; background: #fff; border-radius: 8px; border: 2px solid #e5e7eb;"
                        onerror="this.onerror=null; this.src='{{ asset('images/default-school-icon.svg') }}';"
                    >
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()" @if(!$settings->logo_path) style="display:none;" @endif>Remove Logo</button>
                </div>
            </div>

            <div class="mb-3">
                <label for="motto" class="form-label">Motto</label>
                <input type="text" class="form-control" id="motto" name="motto" value="{{ old('motto', $settings->motto) }}">
            </div>

            <div class="mb-3">
                <label for="vision" class="form-label">Vision</label>
                <textarea class="form-control" id="vision" name="vision" rows="4">{{ old('vision', $settings->vision) }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="school_address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="school_address" name="school_address" value="{{ old('school_address', $settings->school_address) }}">
                </div>
                <div class="col-md-4">
                    <label for="school_phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="school_phone" name="school_phone" value="{{ old('school_phone', $settings->school_phone) }}">
                </div>
                <div class="col-md-4">
                    <label for="school_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="school_email" name="school_email" value="{{ old('school_email', $settings->school_email) }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="exam_duration" class="form-label">Default Exam Duration (minutes)</label>
                    <input type="number" class="form-control" id="exam_duration" name="exam_duration" value="{{ old('exam_duration', $settings->exam_duration) }}" min="1" required>
                </div>
                <div class="col-md-6">
                    <label for="pass_mark" class="form-label">Default Pass Mark (%)</label>
                    <input type="number" class="form-control" id="pass_mark" name="pass_mark" value="{{ old('pass_mark', $settings->pass_mark) }}" min="0" max="100" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="auto_grade" id="auto_grade" value="1" @checked(old('auto_grade', $settings->auto_grade))>
                <label class="form-check-label" for="auto_grade">Auto-grade exams</label>
            </div>

            <button type="submit" class="btn btn-primary-custom">Save School Information</button>
        </form>
    </div>
</div>

<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    if (confirm('Are you sure you want to remove the school logo?')) {
        document.getElementById('logo').value = '';
        document.getElementById('logoPreview').src = '{{ asset('images/default-school-icon.svg') }}';
    }
}
</script>
@endsection
