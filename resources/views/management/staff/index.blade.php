@extends('layouts.admin')

@section('title', 'Teacher & Staff Management')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the highlighted fields.</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-5">
        <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-staff-panel" aria-expanded="false">
            <i class="fas fa-plus me-2"></i>Create Staff
        </button>
        <div class="card collapse d-lg-block {{ $errors->any() && !old('_staff_id') ? 'show' : '' }}" id="create-staff-panel">
            <div class="card-header">Create Staff Member</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.staff.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Portal ID</label>
                        <input type="text" name="portal_id" class="form-control" value="{{ old('portal_id') }}" placeholder="e.g. TCH001" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Jane Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Staff Role</label>
                        <select name="role" class="form-select" required>
                            <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
                            <option value="cbt_personnel" @selected(old('role') === 'cbt_personnel')>CBT Personnel</option>
                            <option value="hod" @selected(old('role') === 'hod')>HOD</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Initial Password</label>
                        <input type="text" name="password" class="form-control" value="{{ old('password', '12345') }}" required>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Staff Permissions</div>
                        @foreach($staffPermissions as $key => [$label, $description])
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="permission-{{ $loop->index }}" @checked(in_array($key, old('permissions', [])))>
                                <label class="form-check-label" for="permission-{{ $loop->index }}">
                                    <span class="fw-semibold">{{ $label }}</span><br>
                                    <small class="text-muted">{{ $description }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">JSS Teaching Subjects (optional)</label>
                        <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                            @foreach($jssSubjects as $subject)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jss_subject_ids[]" value="{{ $subject->id }}" id="create-jss-subject-{{ $subject->id }}" @checked(in_array($subject->id, old('jss_subject_ids', [])))>
                                    <label class="form-check-label" for="create-jss-subject-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">SSS Teaching Subjects (optional)</label>
                        <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                            @foreach($sssSubjects as $subject)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sss_subject_ids[]" value="{{ $subject->id }}" id="create-sss-subject-{{ $subject->id }}" @checked(in_array($subject->id, old('sss_subject_ids', [])))>
                                    <label class="form-check-label" for="create-sss-subject-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                        <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-staff-panel">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Create Staff Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix . '.staff') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Staff name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">All roles</option>
                            <option value="teacher" @selected($selectedRole === 'teacher')>Teacher</option>
                            <option value="cbt_personnel" @selected($selectedRole === 'cbt_personnel')>CBT Personnel</option>
                            <option value="hod" @selected($selectedRole === 'hod')>HOD</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.staff') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        @forelse($staff as $member)
            @php
                $memberPermissionNames = $member->getPermissionNames()->all();
                $memberSubjectIds = $member->teachingSubjects->pluck('id')->all();
                $isEditingOld = (string) old('_staff_id') === (string) $member->id;
                $selectedPermissions = $isEditingOld ? old('permissions', []) : $memberPermissionNames;
                $selectedJssSubjectIds = $isEditingOld ? old('jss_subject_ids', []) : $memberSubjectIds;
                $selectedSssSubjectIds = $isEditingOld ? old('sss_subject_ids', []) : $memberSubjectIds;
            @endphp

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user-tie me-2"></i>
                        {{ $member->full_name }}
                        <span class="badge bg-secondary ms-2">{{ ucwords(str_replace('_', ' ', $member->role)) }}</span>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#staff-edit-{{ $member->id }}" aria-expanded="false" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route($routePrefix . '.staff.destroy', $member) }}" onsubmit="return confirm('Delete this staff member?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($member->teachingSubjects as $subject)
                            <span class="badge bg-light text-dark">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</span>
                        @empty
                            <span class="text-muted">No subjects assigned.</span>
                        @endforelse
                    </div>
                </div>
                <div class="card-body collapse {{ $isEditingOld ? 'show' : '' }}" id="staff-edit-{{ $member->id }}">
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Assigned Classes</div>
                        @forelse($member->assignedClasses as $assignedClass)
                            <form method="POST" action="{{ route($routePrefix . '.staff.classes.unassign', [$member, $assignedClass]) }}" class="d-inline-block mb-2 me-2">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    {{ $assignedClass->full_name }} &times;
                                </button>
                            </form>
                        @empty
                            <span class="text-muted">No class assigned.</span>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route($routePrefix . '.staff.classes.assign', $member) }}" class="row g-2 align-items-end mb-4">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Assign Class</label>
                            <select name="school_class_id" class="form-select" required>
                                <option value="">Select class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary-custom w-100">Assign</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route($routePrefix . '.staff.update', $member) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_staff_id" value="{{ $member->id }}">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Portal ID</label>
                                <input type="text" name="portal_id" class="form-control" value="{{ $isEditingOld ? old('portal_id', $member->portal_id) : $member->portal_id }}" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $isEditingOld ? old('name', $member->full_name) : $member->full_name }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="teacher" @selected(($isEditingOld ? old('role', $member->role) : $member->role) === 'teacher')>Teacher</option>
                                    <option value="cbt_personnel" @selected(($isEditingOld ? old('role', $member->role) : $member->role) === 'cbt_personnel')>CBT</option>
                                    <option value="hod" @selected(($isEditingOld ? old('role', $member->role) : $member->role) === 'hod')>HOD</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="text" name="password" class="form-control" placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="fw-semibold mb-2">Staff Permissions</div>
                            @foreach($staffPermissions as $key => [$label, $description])
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="edit-permission-{{ $member->id }}-{{ $loop->index }}" @checked(in_array($key, $selectedPermissions))>
                                    <label class="form-check-label" for="edit-permission-{{ $member->id }}-{{ $loop->index }}">
                                        <span class="fw-semibold">{{ $label }}</span><br>
                                        <small class="text-muted">{{ $description }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">JSS Teaching Subjects</label>
                                <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                                    @foreach($jssSubjects as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jss_subject_ids[]" value="{{ $subject->id }}" id="edit-jss-subject-{{ $member->id }}-{{ $subject->id }}" @checked(in_array($subject->id, $selectedJssSubjectIds))>
                                            <label class="form-check-label" for="edit-jss-subject-{{ $member->id }}-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SSS Teaching Subjects</label>
                                <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                                    @foreach($sssSubjects as $subject)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sss_subject_ids[]" value="{{ $subject->id }}" id="edit-sss-subject-{{ $member->id }}-{{ $subject->id }}" @checked(in_array($subject->id, $selectedSssSubjectIds))>
                                            <label class="form-check-label" for="edit-sss-subject-{{ $member->id }}-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $member->id }}" @checked($isEditingOld ? old('is_active', false) : $member->is_active)>
                            <label class="form-check-label" for="active-{{ $member->id }}">Active</label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary-custom">Save Staff Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-muted">No staff found.</div>
            </div>
        @endforelse

        {{ $staff->links() }}
    </div>
</div>
@endsection
