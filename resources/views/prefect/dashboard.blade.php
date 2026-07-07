@extends('layouts.admin')

@section('title', 'Prefect Dashboard')

@section('content')
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-user-shield"></i> Student leadership</span>
            <h2>{{ Auth::user()->prefect_title ?? 'Prefect' }} workspace</h2>
            <p>Access exams, attendance, payment records and student directory responsibilities from one polished student leadership view.</p>
            <div class="ts-hero-actions">
                <a href="{{ route('prefect.exams') }}" class="btn btn-light"><i class="fas fa-clipboard-list me-2"></i>Exams</a>
                <a href="{{ route('prefect.students') }}" class="btn btn-outline-light"><i class="fas fa-users me-2"></i>Students</a>
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-award"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-broadcast-tower"></i></span><div class="ts-kpi-value">{{ \App\Models\Exam::where('is_live', true)->count() }}</div><div class="ts-kpi-label">Available exams</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-clipboard-check"></i></span><div class="ts-kpi-value">{{ \App\Models\ExamAttempt::where('student_id', Auth::id())->count() }}</div><div class="ts-kpi-label">Exam attempts</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-id-badge"></i></span><div class="ts-kpi-value">{{ Auth::user()->prefect_title ?? 'Prefect' }}</div><div class="ts-kpi-label">Title</div></div>
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Quick actions</h3>
            <div class="ts-action-grid">
                <a href="{{ route('prefect.exams') }}" class="ts-action"><i class="fas fa-clipboard-list"></i> Exams</a>
                <a href="{{ route('student.payments') }}" class="ts-action"><i class="fas fa-money-bill-wave"></i> Payments</a>
                <a href="{{ route('student.attendance') }}" class="ts-action"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a href="{{ route('prefect.students') }}" class="ts-action"><i class="fas fa-users"></i> Students</a>
            </div>
        </div>
        <div class="ts-panel">
            <h3>Student life</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Exams and records</strong><span>Stay current with your assigned academic tasks.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Leadership access</strong><span>Use the student directory responsibly.</span></div></div>
            </div>
        </div>
    </section>
</div>
@endsection
