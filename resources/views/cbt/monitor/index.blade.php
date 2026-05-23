@extends('layouts.admin')

@section('title', 'Exam Monitor')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Live Exams</span>
                <span class="badge bg-success" id="monitor-status">Live</span>
            </div>
            <div class="card-body" id="live-exams-list">
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Active Attempts</span>
                <small class="text-muted" id="monitor-refreshed-at"></small>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Student</th><th>Class</th><th>Exam</th><th>Started</th><th>Last Seen</th><th>Expires</th></tr></thead>
                    <tbody id="active-attempts-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const monitorDataUrl = @json(route(request()->route()->getName() . '.data'));
let monitorTimer = null;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));
}

function renderLiveExams(exams) {
    const container = document.getElementById('live-exams-list');

    if (!exams.length) {
        container.innerHTML = '<p class="text-muted mb-0">No live exams.</p>';
        return;
    }

    container.innerHTML = exams.map((exam) => `
        <p class="mb-3">
            ${escapeHtml(exam.title)}<br>
            <small>
                ${escapeHtml(exam.subject)} &middot; ${escapeHtml(exam.class)}
                <br>${escapeHtml(exam.active_attempts_count)} active, ${escapeHtml(exam.submitted_attempts_count)} submitted
            </small>
        </p>
    `).join('');
}

function renderActiveAttempts(attempts) {
    const body = document.getElementById('active-attempts-body');

    if (!attempts.length) {
        body.innerHTML = '<tr><td colspan="6" class="text-muted">No active attempts.</td></tr>';
        return;
    }

    body.innerHTML = attempts.map((attempt) => `
        <tr>
            <td>${escapeHtml(attempt.student)}</td>
            <td>${escapeHtml(attempt.class)}</td>
            <td>${escapeHtml(attempt.exam)}</td>
            <td>${escapeHtml(attempt.started_at)}</td>
            <td>${escapeHtml(attempt.last_seen)}</td>
            <td>${escapeHtml(attempt.expires_at)}</td>
        </tr>
    `).join('');
}

async function refreshMonitor() {
    const status = document.getElementById('monitor-status');

    try {
        const response = await fetch(monitorDataUrl, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
        });

        if (!response.ok) {
            throw new Error('Monitor request failed');
        }

        const data = await response.json();
        renderLiveExams(data.live_exams || []);
        renderActiveAttempts(data.active_attempts || []);
        document.getElementById('monitor-refreshed-at').textContent = `Updated ${data.refreshed_at}`;
        status.textContent = 'Live';
        status.className = 'badge bg-success';
    } catch (error) {
        status.textContent = 'Retrying';
        status.className = 'badge bg-warning text-dark';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    refreshMonitor();
    monitorTimer = setInterval(refreshMonitor, 2000);
});

window.addEventListener('beforeunload', () => {
    if (monitorTimer) {
        clearInterval(monitorTimer);
    }
});
</script>
@endsection
