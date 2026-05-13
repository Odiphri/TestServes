@extends('layouts.admin')

@section('title', 'Results Hidden')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Exam Results - {{ $exam->title }}</h5>
                    <small class="text-muted">{{ $exam->subject->name }}</small>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Results Not Available</h4>
                    <p class="text-muted">The results for this exam are not yet available.</p>
                    <p class="text-muted">Please check back later or contact your teacher for more information.</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('student.exams') }}" class="btn btn-primary-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to Exams
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
