@extends('layouts.admin')

@section('title', 'HOD Dashboard')

@section('content')
@php
    $planAccess = app(\App\Support\SchoolPlanAccessService::class);
    $dashboardSchool = app()->bound('currentSchool') ? app('currentSchool') : null;
    $canSeeRoute = fn (string $routeName): bool => $planAccess->allows($dashboardSchool, $planAccess->featureForRoute($routeName));
@endphp
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-sitemap"></i> Department control</span>
            <h2>Department oversight for {{ Auth::user()->full_name }}</h2>
            <p>Review students, exams, overrides, and results from a unified leadership workspace.</p>
            <div class="ts-hero-actions">
                @if($canSeeRoute('hod.students'))
                <a href="{{ route('hod.students') }}" class="btn btn-light"><i class="fas fa-users me-2"></i>Students</a>
                @endif
                @if($canSeeRoute('hod.exams'))
                <a href="{{ route('hod.exams') }}" class="btn btn-outline-light"><i class="fas fa-clipboard-list me-2"></i>Exams</a>
                @endif
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-user-tie"></i></div>
    </section>

    <section class="ts-kpi-grid">
        @if($canSeeRoute('hod.students'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-user-graduate"></i></span><div class="ts-kpi-value">{{ \App\Models\User::where('role', 'student')->count() }}</div><div class="ts-kpi-label">Total students</div></div>
        @endif
        @if($canSeeRoute('hod.exams'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-file-alt"></i></span><div class="ts-kpi-value">{{ \App\Models\Exam::count() }}</div><div class="ts-kpi-label">Total exams</div></div>
        @endif
        @if($canSeeRoute('hod.overrides'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-shield-alt"></i></span><div class="ts-kpi-value">{{ \App\Models\Override::where('is_active', true)->count() }}</div><div class="ts-kpi-label">Active overrides</div></div>
        @endif
        @if($canSeeRoute('hod.results'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-chart-bar"></i></span><div class="ts-kpi-value">{{ \App\Models\ExamAttempt::count() }}</div><div class="ts-kpi-label">Exam attempts</div></div>
        @endif
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Department actions</h3>
            <div class="ts-action-grid">
                @if($canSeeRoute('hod.classes'))<a href="{{ route('hod.classes') }}" class="ts-action"><i class="fas fa-school"></i> Classes</a>@endif
                @if($canSeeRoute('hod.subjects'))<a href="{{ route('hod.subjects') }}" class="ts-action"><i class="fas fa-book"></i> Subjects</a>@endif
                @if($canSeeRoute('hod.overrides'))<a href="{{ route('hod.overrides') }}" class="ts-action"><i class="fas fa-shield-alt"></i> Overrides</a>@endif
                @if($canSeeRoute('hod.results'))<a href="{{ route('hod.results') }}" class="ts-action"><i class="fas fa-chart-bar"></i> Results</a>@endif
            </div>
        </div>
        <div class="ts-panel">
            <h3>Focus queue</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Result oversight</strong><span>Review outcomes, overrides, and live CBT sessions.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>Exam visibility</strong><span>Monitor access, results, and live CBT sessions.</span></div></div>
            </div>
        </div>
    </section>
</div>
@endsection
