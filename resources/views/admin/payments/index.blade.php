@extends('layouts.admin')

@section('title', 'Bursary')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="stat-number">N{{ number_format($totalFees, 2) }}</div>
                <div class="stat-label">Filtered Fees</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="stat-number">N{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-label">Filtered Paid</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="stat-number">N{{ number_format($totalBalance, 2) }}</div>
                <div class="stat-label">Filtered Balance</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <button class="btn btn-primary-custom w-100 mb-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#create-fee-panel" aria-expanded="false">
            <i class="fas fa-plus me-2"></i>Create Fee
        </button>
        <div class="card collapse d-lg-block {{ $errors->any() ? 'show' : '' }}" id="create-fee-panel">
            <div class="card-header">Set Fee</div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.payments.fees.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Fee Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fee Type</label>
                        <select name="fee_type" class="form-select" required>
                            <option value="compulsory" @selected(old('fee_type') === 'compulsory')>Compulsory</option>
                            <option value="optional" @selected(old('fee_type') === 'optional')>Optional</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="applies_to_all_classes" value="1" class="form-check-input" id="all-classes" @checked(old('applies_to_all_classes', true))>
                        <label class="form-check-label" for="all-classes">Apply to all classes</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Specific Classes</label>
                        <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
                            @foreach($classes as $class)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="class_ids[]" value="{{ $class->id }}" id="fee-class-{{ $class->id }}" @checked(in_array($class->id, old('class_ids', [])))>
                                    <label class="form-check-label" for="fee-class-{{ $class->id }}">{{ $class->full_name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-light d-none d-lg-inline-block">Cancel</button>
                        <button type="reset" class="btn btn-light d-lg-none" data-bs-toggle="collapse" data-bs-target="#create-fee-panel">Cancel</button>
                        <button class="btn btn-primary-custom flex-grow-1">Create Fee</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Fees</div>
            <div class="card-body">
                @forelse($feeItems as $fee)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between gap-2 align-items-start">
                            <div>
                                <div class="fw-semibold">
                                    {{ $fee->name }}
                                    <span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $fee->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <small class="text-muted">
                                    N{{ number_format($fee->amount, 2) }} · {{ ucfirst($fee->fee_type) }}
                                    · {{ $fee->applies_to_all_classes ? 'All classes' : $fee->classes->pluck('full_name')->join(', ') }}
                                </small>
                            </div>
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route($routePrefix . '.payments.fees.toggle', $fee) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $fee->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $fee->is_active ? 'Make inactive' : 'Make active' }}">
                                        <i class="fas {{ $fee->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($routePrefix . '.payments.fees.destroy', $fee) }}" onsubmit="return confirm('Permanently delete this fee? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No fees have been set.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Student Payments</div>
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix . '.payments') }}" class="row g-2 align-items-end mb-3" data-auto-submit="true">
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
                        <button class="btn btn-primary-custom flex-grow-1">Sort</button>
                        <a href="{{ route($routePrefix . '.payments') }}" class="btn btn-light">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th class="text-end">Fees</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">%</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                @php($summary = $student->fee_summary)
                                <tr>
                                    <td class="fw-semibold">{{ $student->full_name }}</td>
                                    <td>{{ $student->assignedClass->full_name ?? 'No class' }}</td>
                                    <td class="text-end">N{{ number_format($summary['total_due'], 2) }}</td>
                                    <td class="text-end">N{{ number_format($summary['amount_paid'], 2) }}</td>
                                    <td class="text-end">N{{ number_format($summary['balance'], 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $summary['balance'] > 0 ? 'bg-warning text-dark' : 'bg-success' }}">{{ $summary['paid_percent'] }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route($routePrefix . '.payments.students.show', $student) }}" class="btn btn-sm btn-primary-custom">Update</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
