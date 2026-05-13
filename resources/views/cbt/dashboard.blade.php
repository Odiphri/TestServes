@extends('layouts.admin')

@section('title', 'CBT Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">CBT Dashboard</h5>
                </div>
                <div class="card-body">
                    <h6>Welcome, {{ Auth::user()->full_name }}</h6>
                    <p class="text-muted">Computer Based Testing Personnel Dashboard</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\Exam::count() }}</h3>
                                    <p>Total Exams</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\Question::count() }}</h3>
                                    <p>Total Questions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\ExamAttempt::count() }}</h3>
                                    <p>Exam Attempts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
