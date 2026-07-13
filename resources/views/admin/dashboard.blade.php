@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $planAccess = app(\App\Support\SchoolPlanAccessService::class);
    $dashboardSchool = app()->bound('currentSchool') ? app('currentSchool') : null;
    $canSeeRoute = fn (string $routeName): bool => $planAccess->allows($dashboardSchool, $planAccess->featureForRoute($routeName));
@endphp
<div class="ts-dashboard">
    <section class="ts-hero">
        <div>
            <span class="ts-eyebrow"><i class="fas fa-command"></i> Executive workspace</span>
            <h2>Run the entire school platform from one modern command center.</h2>
            <p>Users, classes, CBT, payments, exams, and reports sit inside a cleaner enterprise dashboard built for fast decisions.</p>
            <div class="ts-hero-actions">
                @if($canSeeRoute('admin.users'))
                <a href="{{ route('admin.users') }}" class="btn btn-light"><i class="fas fa-users me-2"></i>Manage users</a>
                @endif
                @if($canSeeRoute('admin.exams'))
                <a href="{{ route('admin.exams') }}" class="btn btn-outline-light"><i class="fas fa-clipboard-list me-2"></i>Exam center</a>
                @endif
            </div>
        </div>
        <div class="ts-hero-badge"><i class="fas fa-chart-pie"></i></div>
    </section>

    <section class="ts-kpi-grid">
        <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-users"></i></span><div class="ts-kpi-value">{{ $stats['total_users'] }}</div><div class="ts-kpi-label">Total users</div></div>
        @if($canSeeRoute('admin.classes'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-school"></i></span><div class="ts-kpi-value">{{ $stats['total_classes'] }}</div><div class="ts-kpi-label">Classes</div></div>
        @endif
        @if($canSeeRoute('admin.exams'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-clipboard-list"></i></span><div class="ts-kpi-value">{{ $stats['total_exams'] }}</div><div class="ts-kpi-label">Exams</div></div>
        @endif
        @if($canSeeRoute('admin.payments'))
            <div class="ts-kpi"><span class="ts-kpi-icon"><i class="fas fa-credit-card"></i></span><div class="ts-kpi-value">{{ round($paymentStats['collection_rate'], 0) }}%</div><div class="ts-kpi-label">Payment rate</div></div>
        @endif
    </section>

    <section class="ts-panel-grid">
        <div class="ts-panel">
            <h3>Recent users</h3>
            <div class="ts-timeline">
                @forelse($recentUsers as $user)
                    <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>{{ $user->full_name }}</strong><span>{{ $user->email ?? ucwords(str_replace('_', ' ', $user->role)) }}</span></div></div>
                @empty
                    <div class="text-muted">No recent users</div>
                @endforelse
            </div>
        </div>
        @if($canSeeRoute('admin.exams') || $canSeeRoute('admin.results'))
        <div class="ts-panel">
            <h3>Exam snapshot</h3>
            <div class="ts-timeline">
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>{{ $stats['active_exams'] }} active exams</strong><span>Monitor live CBT sessions and recent attempts.</span></div></div>
                <div class="ts-timeline-item"><span class="ts-timeline-dot"></span><div><strong>{{ $stats['total_attempts'] }} attempts recorded</strong><span>Review performance from the results center.</span></div></div>
            </div>
        </div>
        @endif
    </section>

    @if($canSeeRoute('admin.exams'))
    <section class="ts-panel">
        <h3>Recent exams</h3>
        <div class="ts-action-grid">
            @forelse($recentExams as $exam)
                <a href="{{ route('admin.exams.show', $exam) }}" class="ts-action"><i class="fas fa-file-alt"></i> {{ $exam->title }}</a>
            @empty
                <div class="text-muted">No recent exams</div>
            @endforelse
        </div>
    </section>
    @endif
</div>
@endsection
