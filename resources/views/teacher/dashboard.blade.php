@extends('layouts.admin')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-chalkboard-teacher"></i> Teaching workspace</span>
            <h2>Welcome back, {{ Auth::user()->full_name }}</h2>
            <p>Track exams, class activity, attendance and student performance from a cleaner teaching command center.</p>
            <div class="ts-hero-actions">
                <a href="{{ route('teacher.exams') }}" class="btn btn-light"><i class="fas fa-clipboard-list me-2"></i>Manage exams</a>
                <a href="{{ route('teacher.students') }}" class="btn btn-outline-light"><i class="fas fa-users me-2"></i>View students</a>
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-pen-nib"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi">
            <span class="ts-kpi-icon"><i class="fas fa-file-alt"></i></span>
            <div class="ts-kpi-value">{{ $myExamsCount }}</div>
            <div class="ts-kpi-label">My exams</div>
        </div>
        <div class="ts-kpi">
            <span class="ts-kpi-icon"><i class="fas fa-broadcast-tower"></i></span>
            <div class="ts-kpi-value">{{ $liveExamsCount }}</div>
            <div class="ts-kpi-label">Live exams</div>
        </div>
        <div class="ts-kpi">
            <span class="ts-kpi-icon"><i class="fas fa-calendar-check"></i></span>
            <div class="ts-kpi-value">{{ $attendanceMarkedCount }}</div>
            <div class="ts-kpi-label">Attendance marked</div>
        </div>
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Quick actions</h3>
            <div class="ts-action-grid">
                <a href="{{ route('teacher.exams') }}" class="ts-action"><i class="fas fa-clipboard-list"></i> Exams</a>
                <a href="{{ route('teacher.results') }}" class="ts-action"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="{{ route('teacher.classes') }}" class="ts-action"><i class="fas fa-school"></i> My classes</a>
                <a href="{{ route('teacher.profile.edit') }}" class="ts-action"><i class="fas fa-user-cog"></i> Profile</a>
            </div>
        </div>
        <div class="ts-panel">
            <h3>Today</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Exam workspace ready</strong><span>Create, monitor, and review class assessments.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Attendance tools online</strong><span>Keep daily records aligned with your assigned classes.</span></div></div>
            </div>
        </div>
    </section>
</div>
@endsection
