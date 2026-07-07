@extends('layouts.admin')

@section('title', 'Student Dashboard')

@section('content')
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-user-graduate"></i> Student portal</span>
            <h2>Welcome, {{ Auth::user()->full_name }}</h2>
            <p>{{ Auth::user()->schoolClass->full_name ?? 'No class assigned' }}. Your exams, payments, attendance and profile tasks are organized in one clean workspace.</p>
            <div class="ts-hero-actions">
                <a href="{{ route('student.exams') }}" class="btn btn-light"><i class="fas fa-clipboard-list me-2"></i>Start exams</a>
                <a href="{{ route('student.profile.edit') }}" class="btn btn-outline-light"><i class="fas fa-user-cog me-2"></i>Update profile</a>
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-graduation-cap"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-broadcast-tower"></i></span><div class="ts-kpi-value">{{ $examStats['total'] }}</div><div class="ts-kpi-label">Available exams</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-clipboard-check"></i></span><div class="ts-kpi-value">{{ \App\Models\ExamAttempt::where('student_id', Auth::id())->count() }}</div><div class="ts-kpi-label">Exam attempts</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-credit-card"></i></span><div class="ts-kpi-value">{{ \App\Models\Payment::where('student_id', Auth::id())->where('status', 'paid')->count() }}</div><div class="ts-kpi-label">Paid fees</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-calendar-check"></i></span><div class="ts-kpi-value">{{ \App\Models\Attendance::where('student_id', Auth::id())->where('status', 'present')->count() }}</div><div class="ts-kpi-label">Present days</div></div>
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Quick actions</h3>
            <div class="ts-action-grid">
                <a href="{{ route('student.exams') }}" class="ts-action"><i class="fas fa-clipboard-list"></i> Exams</a>
                <a href="{{ route('student.payments') }}" class="ts-action"><i class="fas fa-money-bill-wave"></i> Payments</a>
                <a href="{{ route('student.attendance') }}" class="ts-action"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a href="{{ route('student.profile.edit') }}" class="ts-action"><i class="fas fa-user-cog"></i> Profile</a>
            </div>
        </div>
        <div class="ts-panel">
            <h3>Recent activity</h3>
            <div class="ts-timeline">
                @forelse($recentExams->take(3) as $exam)
                    <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>{{ $exam->title }}</strong><span>{{ $exam->created_at->format('M d, Y') }}</span></div></div>
                @empty
                    <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>No recent activity</strong><span>New exams and notices will appear here.</span></div></div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
