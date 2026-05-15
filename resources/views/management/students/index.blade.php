@extends('layouts.admin')

@section('title', 'Manage Students')

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
        <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-student-panel" aria-expanded="false">
            <i class="fas fa-plus me-2"></i>Create Student
        </button>
        <div class="card collapse d-lg-block {{ $errors->any() && !old('_student_id') ? 'show' : '' }}" id="create-student-panel">
            <div class="card-header">Add New Student</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.students.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="portal_id" class="form-control" value="{{ old('portal_id') }}" placeholder="e.g. 10500" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Class / Section</label>
                        <select name="school_class_id" class="form-select student-class-select" data-subject-select="create-subjects" required>
                            <option value="">Select class section</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" id="account-type" class="form-select" required>
                            <option value="student" @selected(old('account_type', 'student') === 'student')>Student</option>
                            <option value="prefect" @selected(old('account_type') === 'prefect')>Prefect</option>
                        </select>
                    </div>

                    <div class="mb-3" id="class-role-wrap">
                        <label class="form-label">Class Role</label>
                        <select name="student_role_id" class="form-select">
                            <option value="">Select class role</option>
                            @foreach($studentRoles as $studentRole)
                                <option value="{{ $studentRole->id }}" @selected(old('student_role_id') == $studentRole->id)>{{ $studentRole->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="prefect-role-wrap">
                        <label class="form-label">Prefect Role</label>
                        <select name="prefect_role_id" class="form-select">
                            <option value="">Select prefect role</option>
                            @foreach($prefectRoles as $prefectRole)
                                <option value="{{ $prefectRole->id }}" @selected(old('prefect_role_id') == $prefectRole->id)>{{ $prefectRole->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" value="{{ old('age') }}" min="1" max="120" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select" required>
                                <option value="">Select</option>
                                <option value="male" @selected(old('sex') === 'male')>Male</option>
                                <option value="female" @selected(old('sex') === 'female')>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Complexion</label>
                            <input type="text" name="complexion" class="form-control" value="{{ old('complexion') }}" placeholder="e.g. Fair" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subjects</label>
                        <div id="create-subjects" class="student-subject-checks border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                            @foreach($subjects as $subject)
                                <div class="form-check subject-check-row" data-class-id="{{ $subject->school_class_id }}">
                                    <input class="form-check-input" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" id="create-subject-{{ $subject->id }}" @checked(in_array($subject->id, old('subject_ids', [])))>
                                    <label class="form-check-label" for="create-subject-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Initial Password</label>
                        <input type="text" name="password" class="form-control" value="{{ old('password', '12345') }}" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                        <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-student-panel">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Create Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix . '.students') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Student name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>{{ $class->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom flex-grow-1">Search</button>
                        <a href="{{ route($routePrefix . '.students') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        @forelse($students as $student)
            @php
                $studentSubjectIds = $student->subjects->pluck('id')->all();
                $isEditingOld = (string) old('_student_id') === (string) $student->id;
                $selectedSubjectIds = $isEditingOld ? old('subject_ids', $studentSubjectIds) : $studentSubjectIds;
                $position = $student->role === 'prefect'
                    ? ($student->prefectRole->name ?? $student->prefect_title ?? 'Prefect')
                    : ($student->studentRole->name ?? 'Student');
            @endphp

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        @if($student->profile?->profile_picture)
                            <img src="{{ $student->profile->profile_picture_url }}" alt="{{ $student->full_name }}" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover;">
                        @else
                            <i class="fas fa-user-graduate me-2"></i>
                        @endif
                        <span>{{ $student->full_name }}</span>
                        <span class="badge bg-secondary ms-2">{{ ucfirst($student->role) }}</span>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#student-edit-{{ $student->id }}" aria-expanded="false" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route($routePrefix . '.students.destroy', $student) }}" onsubmit="return confirm('Delete this student?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-light text-dark">{{ $student->assignedClass->full_name ?? 'Unassigned' }}</span>
                        <span class="badge bg-light text-dark">{{ $position }}</span>
                        <span class="badge {{ $student->is_active ? 'bg-success' : 'bg-danger' }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="mt-2">
                        @forelse($student->subjects as $subject)
                            <span class="badge bg-light text-dark me-1 mb-1">{{ $subject->name }}</span>
                        @empty
                            <span class="text-muted">No subjects assigned.</span>
                        @endforelse
                    </div>
                </div>
                <div class="card-body collapse {{ $isEditingOld ? 'show' : '' }}" id="student-edit-{{ $student->id }}">
                    <form method="POST" action="{{ route($routePrefix . '.students.update', $student) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_student_id" value="{{ $student->id }}">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Student ID</label>
                                <input type="text" name="portal_id" class="form-control" value="{{ $isEditingOld ? old('portal_id', $student->portal_id) : $student->portal_id }}" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $isEditingOld ? old('name', $student->full_name) : $student->full_name }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Account</label>
                                <select name="account_type" class="form-select edit-account-type" data-student="{{ $student->id }}" required>
                                    <option value="student" @selected(($isEditingOld ? old('account_type', $student->role) : $student->role) === 'student')>Student</option>
                                    <option value="prefect" @selected(($isEditingOld ? old('account_type', $student->role) : $student->role) === 'prefect')>Prefect</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Class / Section</label>
                                <select name="school_class_id" class="form-select student-class-select" data-subject-select="edit-subjects-{{ $student->id }}" required>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected(($isEditingOld ? old('school_class_id', $student->school_class_id) : $student->school_class_id) == $class->id)>{{ $class->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 edit-class-role-wrap" data-student="{{ $student->id }}">
                                <label class="form-label">Class Role</label>
                                <select name="student_role_id" class="form-select">
                                    <option value="">Select class role</option>
                                    @foreach($studentRoles as $studentRole)
                                        <option value="{{ $studentRole->id }}" @selected(($isEditingOld ? old('student_role_id', $student->student_role_id) : $student->student_role_id) == $studentRole->id)>{{ $studentRole->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 edit-prefect-role-wrap" data-student="{{ $student->id }}">
                                <label class="form-label">Prefect Role</label>
                                <select name="prefect_role_id" class="form-select">
                                    <option value="">Select prefect role</option>
                                    @foreach($prefectRoles as $prefectRole)
                                        <option value="{{ $prefectRole->id }}" @selected(($isEditingOld ? old('prefect_role_id', $student->prefect_role_id) : $student->prefect_role_id) == $prefectRole->id)>{{ $prefectRole->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" class="form-control" value="{{ $isEditingOld ? old('age', $student->profile->age ?? '') : ($student->profile->age ?? '') }}" min="1" max="120" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sex</label>
                                <select name="sex" class="form-select" required>
                                    <option value="male" @selected(($isEditingOld ? old('sex', $student->profile->gender ?? '') : ($student->profile->gender ?? '')) === 'male')>Male</option>
                                    <option value="female" @selected(($isEditingOld ? old('sex', $student->profile->gender ?? '') : ($student->profile->gender ?? '')) === 'female')>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Complexion</label>
                                <input type="text" name="complexion" class="form-control" value="{{ $isEditingOld ? old('complexion', $student->profile->complexion ?? '') : ($student->profile->complexion ?? '') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subjects</label>
                            <div id="edit-subjects-{{ $student->id }}" class="student-subject-checks border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                @foreach($subjects as $subject)
                                    <div class="form-check subject-check-row" data-class-id="{{ $subject->school_class_id }}">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" id="edit-subject-{{ $student->id }}-{{ $subject->id }}" @checked(in_array($subject->id, $selectedSubjectIds))>
                                        <label class="form-check-label" for="edit-subject-{{ $student->id }}-{{ $subject->id }}">{{ $subject->name }} - {{ $subject->schoolClass->full_name ?? 'No class' }}</label>
                                    </div>
                                @endforeach
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

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="student-active-{{ $student->id }}" @checked($student->is_active)>
                            <label class="form-check-label" for="student-active-{{ $student->id }}">Active</label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary-custom">Save Student Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-muted">No students found.</div>
            </div>
        @endforelse

        {{ $students->links() }}
    </div>
</div>

<script>
    const accountType = document.getElementById('account-type');
    const classRoleWrap = document.getElementById('class-role-wrap');
    const prefectRoleWrap = document.getElementById('prefect-role-wrap');

    function syncRoleFields() {
        const isPrefect = accountType.value === 'prefect';
        classRoleWrap.style.display = isPrefect ? 'none' : 'block';
        prefectRoleWrap.style.display = isPrefect ? 'block' : 'none';
    }

    accountType.addEventListener('change', syncRoleFields);
    syncRoleFields();

    function syncEditRoleFields(select) {
        const studentId = select.dataset.student;
        const isPrefect = select.value === 'prefect';
        const classRole = document.querySelector(`.edit-class-role-wrap[data-student="${studentId}"]`);
        const prefectRole = document.querySelector(`.edit-prefect-role-wrap[data-student="${studentId}"]`);

        if (classRole && prefectRole) {
            classRole.style.display = isPrefect ? 'none' : 'block';
            prefectRole.style.display = isPrefect ? 'block' : 'none';
        }
    }

    document.querySelectorAll('.edit-account-type').forEach((select) => {
        select.addEventListener('change', () => syncEditRoleFields(select));
        syncEditRoleFields(select);
    });

    function filterSubjectsForClass(classSelect) {
        const subjectSelect = document.getElementById(classSelect.dataset.subjectSelect);

        if (!subjectSelect) {
            return;
        }

        const selectedClassId = classSelect.value;

        subjectSelect.querySelectorAll('.subject-check-row').forEach((row) => {
            const isForClass = row.dataset.classId === selectedClassId;
            row.hidden = !isForClass;

            if (!isForClass) {
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        });
    }

    document.querySelectorAll('.student-class-select').forEach((select) => {
        select.addEventListener('change', () => filterSubjectsForClass(select));
        filterSubjectsForClass(select);
    });
</script>
@endsection
