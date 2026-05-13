@extends('layouts.admin')

@section('title', 'My Exams')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Exams</h5>
                </div>
                <div class="card-body">
                    @if(($noClass ?? false))
                        <div class="text-center py-5">
                            <i class="fas fa-school fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No class assigned</h5>
                            <p class="text-muted">You will see exams here after you have been assigned to a class.</p>
                        </div>
                    @else
                        @if(($isOwing ?? false) && !($hasActiveOverride ?? false))
                            <div class="alert alert-danger">
                                You have outstanding fees. You cannot take exams until payment is completed or an override is granted.
                            </div>
                        @elseif(($isOwing ?? false) && ($hasActiveOverride ?? false))
                            <div class="alert alert-info">
                                You have outstanding fees, but an active override allows access to the approved exam shown below.
                            </div>
                        @endif
                    @endif

                    @if(!($noClass ?? false))
                    @if($exams->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No available exams</h5>
                            <p class="text-muted">Only live exams assigned to your class will appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Exam Title</th>
                                        <th>Subject</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exams as $exam)
                                    <tr>
                                        <td>
                                            <strong>{{ $exam->title }}</strong>
                                            @if($exam->description)
                                                <br><small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $exam->subject->name }}</td>
                                        <td>{{ $exam->duration_minutes }} minutes</td>
                                        <td>
                                            @if($exam->attempted)
                                                @if($exam->attempt->is_submitted)
                                                    <span class="badge badge-success">Completed</span>
                                                @else
                                                    <span class="badge badge-warning">In Progress</span>
                                                @endif
                                            @else
                                                @if($exam->can_access)
                                                    <span class="badge badge-primary">Available</span>
                                                    @if($exam->is_owing && $exam->active_override)
                                                        <span class="badge bg-info text-dark">Override Granted</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-danger">Blocked</span>
                                                    @if($exam->is_owing)
                                                        <br><small class="text-muted">Payment pending, no active override</small>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($exam->attempted && $exam->attempt->is_submitted)
                                                <a href="{{ route('student.exams.results', $exam->id) }}" class="btn btn-sm btn-primary-custom">
                                                    View Results
                                                </a>
                                            @elseif($exam->can_access)
                                                <a href="{{ route('student.exams.show', $exam->id) }}" class="btn btn-sm btn-primary-custom">
                                                    @if($exam->attempted && !$exam->attempt->is_submitted)
                                                        Continue Exam
                                                    @else
                                                        Take Exam
                                                    @endif
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    Not Available
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
