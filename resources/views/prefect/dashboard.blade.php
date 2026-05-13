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
                    <p class="text-muted">Student management dashboard</p>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\User::where('role', 'student')->count() }}</h3>
                                    <p>Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3>{{ \App\Models\SchoolClass::count() }}</h3>
                                    <p>Total Classes</p>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
