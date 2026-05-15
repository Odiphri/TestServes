@extends('layouts.admin')

@section('title', 'Prefect Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Prefect Dashboard</h5>
                </div>
                <div class="card-body">
                    <h6>Welcome, {{ Auth::user()->full_name }}</h6>
                    <p class="text-muted">Prefect and student dashboard</p>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\Exam::where('is_live', true)->count() }}</h3>
                                    <p>Available Exams</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\ExamAttempt::where('student_id', Auth::id())->count() }}</h3>
                                    <p>Exam Attempts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ Auth::user()->prefect_title ?? 'Prefect' }}</h3>
                                    <p>Title</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('prefect.exams') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-clipboard-list me-2"></i> Exams
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('student.payments') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-money-bill-wave me-2"></i> Payments
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('student.attendance') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-calendar-check me-2"></i> Attendance
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('prefect.students') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-users me-2"></i> Students
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
