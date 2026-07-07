@extends('layouts.admin')

@section('title', 'HOD Dashboard')

@section('content')
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-sitemap"></i> Department control</span>
            <h2>Department oversight for {{ Auth::user()->full_name }}</h2>
            <p>Review students, exams, overrides and pending requests from a unified leadership workspace.</p>
            <div class="ts-hero-actions">
                <a href="{{ route('hod.students') }}" class="btn btn-light"><i class="fas fa-users me-2"></i>Students</a>
                <a href="{{ route('hod.exams') }}" class="btn btn-outline-light"><i class="fas fa-clipboard-list me-2"></i>Exams</a>
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-user-tie"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-user-graduate"></i></span><div class="ts-kpi-value">{{ \App\Models\User::where('role', 'student')->count() }}</div><div class="ts-kpi-label">Total students</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-file-alt"></i></span><div class="ts-kpi-value">{{ \App\Models\Exam::count() }}</div><div class="ts-kpi-label">Total exams</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-shield-alt"></i></span><div class="ts-kpi-value">{{ \App\Models\Override::where('is_active', true)->count() }}</div><div class="ts-kpi-label">Active overrides</div></div>
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-inbox"></i></span><div class="ts-kpi-value">{{ \App\Models\ChangeRequest::where('status', 'pending')->count() }}</div><div class="ts-kpi-label">Pending requests</div></div>
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Department actions</h3>
            <div class="ts-action-grid">
                <a href="{{ route('hod.classes') }}" class="ts-action"><i class="fas fa-school"></i> Classes</a>
                <a href="{{ route('hod.subjects') }}" class="ts-action"><i class="fas fa-book"></i> Subjects</a>
                <a href="{{ route('hod.overrides') }}" class="ts-action"><i class="fas fa-shield-alt"></i> Overrides</a>
                <a href="{{ route('hod.results') }}" class="ts-action"><i class="fas fa-chart-bar"></i> Results</a>
            </div>
        </div>
        <div class="ts-panel">
            <h3>Focus queue</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Requests awaiting review</strong><span>Resolve student profile and academic changes.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Exam visibility</strong><span>Monitor access, results, and live CBT sessions.</span></div></div>
            </div>
        </div>
    </section>
</div>
@endsection
