@extends('layouts.admin')

@section('title', 'HOD Overrides')

@section('content')
<button class="btn btn-primary-custom mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#create-override-panel" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
    <i class="fas fa-plus me-2"></i>Create Override
</button>

<div class="card collapse {{ $errors->any() ? 'show' : '' }}" id="create-override-panel">
    <div class="card-header">Create Override</div>
    <div class="card-body">
        <form method="POST" action="{{ route(($routePrefix ?? 'hod') . '.overrides.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Student Login ID</label>
                    <input type="text" name="student_portal_id" class="form-control" value="{{ old('student_portal_id') }}" list="override-student-ids" placeholder="Enter student ID" required>
                    <datalist id="override-student-ids">
                        @foreach($students as $student)
                            <option value="{{ $student->portal_id }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}" list="override-student-names" placeholder="Enter student name" required>
                    <datalist id="override-student-names">
                        @foreach($students as $student)
                            <option value="{{ $student->full_name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Exam</label>
                    <select name="exam_id" class="form-select">
                        <option value="">All exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" @selected((string) old('exam_id') === (string) $exam->id)>
                                {{ $exam->title }}{{ $exam->schoolClass ? ' - ' . $exam->schoolClass->full_name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry</label>
                    <input type="datetime-local" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="2" required>{{ old('reason') }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-3">
                <button type="reset" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#create-override-panel">Cancel</button>
                <button class="btn btn-primary-custom">Create Override</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Exam Access Overrides</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Exam</th>
                        <th>Reason</th>
                        <th>Created By</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overrides as $override)
                        <tr>
                            <td>{{ $override->student->full_name ?? 'N/A' }}</td>
                            <td>{{ $override->exam->title ?? 'All exams' }}</td>
                            <td>{{ $override->reason }}</td>
                            <td>{{ $override->approver->full_name ?? 'N/A' }}</td>
                            <td>{{ $override->expiry_date->format('M d, Y') }}</td>
                            <td>{{ $override->isActive() ? 'Active' : 'Expired' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route(($routePrefix ?? 'hod') . '.overrides.destroy', $override) }}" onsubmit="return confirm('Delete this override?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No overrides found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $overrides->links() }}
    </div>
</div>
@endsection
