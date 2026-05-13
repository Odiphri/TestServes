@extends('layouts.admin')

@section('title', 'Attendance')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Attendance</h5>
    </div>
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
        </div>
        <h4 class="text-muted mb-3">No Class Assignment</h4>
        <p class="text-muted mb-4">
            You have not been assigned to any class yet. Please contact the administrator to get a class assignment before you can mark attendance.
        </p>
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Next Steps:</h6>
            <ul class="text-start mb-0">
                <li>Contact the school administrator</li>
                <li>Request a class assignment</li>
                <li>Once assigned, you can mark attendance for your class</li>
            </ul>
        </div>
    </div>
</div>
@endsection
