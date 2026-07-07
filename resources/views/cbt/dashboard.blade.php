@extends('layouts.admin')

@section('title', 'CBT Dashboard')

@section('content')
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-desktop"></i> Exam operations</span>
            <h2>CBT control room</h2>
            <p>Manage exams, questions, attempts, monitoring and result operations from a focused testing command center.</p>
            <div class="ts-hero-actions">
                <a href="{{ route('cbt.exams') }}" class="btn btn-light"><i class="fas fa-clipboard-list me-2"></i>Manage exams</a>
                <a href="{{ route('cbt.monitor') }}" class="btn btn-outline-light"><i class="fas fa-eye me-2"></i>Monitor live exams</a>
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-laptop-code"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-file-alt"></i></span><div class="ts-kpi-value">{{ \App\Models\Exam::count() }}</div><div class="ts-kpi-label">Total exams</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-question-circle"></i></span><div class="ts-kpi-value">{{ \App\Models\Question::count() }}</div><div class="ts-kpi-label">Questions</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-user-check"></i></span><div class="ts-kpi-value">{{ \App\Models\ExamAttempt::count() }}</div><div class="ts-kpi-label">Exam attempts</div></div>
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>CBT shortcuts</h3>
            <div class="ts-action-grid">
                <a href="{{ route('cbt.exams') }}" class="ts-action"><i class="fas fa-clipboard-list"></i> Exams</a>
                <a href="{{ route('cbt.monitor') }}" class="ts-action"><i class="fas fa-eye"></i> Monitor</a>
                <a href="{{ route('cbt.results') }}" class="ts-action"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="{{ route('traffic.index') }}" class="ts-action"><i class="fas fa-chart-line"></i> Traffic</a>
            </div>
        </div>
        <div class="ts-panel">
            <h3>Exam readiness</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Question bank available</strong><span>Keep assessment content organized and reviewable.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Monitoring tools online</strong><span>Track attempts and exam activity in real time.</span></div></div>
            </div>
        </div>
    </section>
</div>
@endsection
