@extends('layouts.admin')

@section('title', 'Traffic & Analytics')

@section('content')
<div class="traffic-shell" data-traffic-url="{{ route('traffic.data') }}">
    <div class="traffic-toolbar">
        <div>
            <label class="form-label">Time Range</label>
            <select class="form-select" id="traffic-range">
                <option value="live">Live / Real-time</option>
                <option value="minutes">Last minutes</option>
                <option value="hourly">Hourly</option>
                <option value="daily" selected>Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </div>
        <div id="minutes-control">
            <label class="form-label">Minutes</label>
            <input class="form-control" type="number" min="1" max="1440" value="30" id="traffic-minutes">
        </div>
        <div>
            <label class="form-label">Role</label>
            <select class="form-select" id="traffic-role">
                <option value="">All roles</option>
            </select>
        </div>
    </div>

    <div class="traffic-stats">
        <div class="traffic-stat">
            <span>Total visitors</span>
            <strong id="traffic-total">0</strong>
        </div>
        <div class="traffic-stat">
            <span>Online now</span>
            <strong id="traffic-online">0</strong>
        </div>
        <div class="traffic-stat">
            <span>Peak usage</span>
            <strong id="traffic-peak">No activity yet</strong>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Traffic Volume</div>
                <div class="card-body">
                    <canvas id="traffic-chart" height="260"></canvas>
                    <div class="text-muted py-4 text-center d-none" id="traffic-empty">No traffic data for this filter.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Role Breakdown</div>
                <div class="card-body">
                    <div id="role-breakdown" class="role-breakdown"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recent Visitors</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Visit Time</th>
                            <th>Duration</th>
                            <th>Device</th>
                            <th>IP</th>
                            <th>Pages</th>
                        </tr>
                    </thead>
                    <tbody id="recent-visitors">
                        <tr><td colspan="7" class="text-muted">Loading traffic data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.traffic-shell {
    display: grid;
    gap: 18px;
}

.traffic-toolbar,
.traffic-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.traffic-stat {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}

.traffic-stat span {
    display: block;
    color: #6c757d;
    font-size: .8rem;
    margin-bottom: 4px;
}

.traffic-stat strong {
    color: #0a1931;
    font-size: 1.35rem;
}

.role-breakdown {
    display: grid;
    gap: 12px;
}

.role-row {
    display: grid;
    gap: 6px;
}

.role-row-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: .9rem;
}

.role-bar {
    height: 10px;
    background: #edf1f5;
    border-radius: 999px;
    overflow: hidden;
}

.role-bar span {
    display: block;
    height: 100%;
    background: #0a1931;
}

.page-chip {
    display: inline-block;
    padding: 2px 7px;
    margin: 1px;
    border-radius: 999px;
    background: #eef3f8;
    color: #0a1931;
    font-size: .75rem;
}

@media (max-width: 768px) {
    .traffic-toolbar,
    .traffic-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.traffic-shell');
    const canvas = document.getElementById('traffic-chart');
    const ctx = canvas.getContext('2d');
    const rangeSelect = document.getElementById('traffic-range');
    const minutesInput = document.getElementById('traffic-minutes');
    const roleSelect = document.getElementById('traffic-role');
    const minutesControl = document.getElementById('minutes-control');
    let refreshTimer;

    function roleLabel(role) {
        return role.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function drawChart(series) {
        const width = canvas.clientWidth;
        const height = canvas.height;
        canvas.width = width * window.devicePixelRatio;
        canvas.height = height * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
        ctx.clearRect(0, 0, width, height);

        document.getElementById('traffic-empty').classList.toggle('d-none', series.length > 0);
        if (!series.length) return;

        const padding = 34;
        const chartWidth = width - padding * 2;
        const chartHeight = height - padding * 2;
        const maxVisits = Math.max(...series.map((item) => item.visits), 1);
        const barGap = 8;
        const barWidth = Math.max(10, (chartWidth / series.length) - barGap);

        ctx.strokeStyle = '#d9e1ea';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, height - padding);
        ctx.lineTo(width - padding, height - padding);
        ctx.stroke();

        ctx.fillStyle = '#0a1931';
        series.forEach((item, index) => {
            const x = padding + index * (barWidth + barGap);
            const barHeight = (item.visits / maxVisits) * chartHeight;
            const y = height - padding - barHeight;
            ctx.fillRect(x, y, barWidth, barHeight);

            ctx.fillStyle = '#6c757d';
            ctx.font = '11px Segoe UI';
            ctx.fillText(item.label, x, height - 10, Math.max(40, barWidth + barGap));
            ctx.fillStyle = '#0a1931';
        });
    }

    function renderRoles(breakdown, total) {
        const target = document.getElementById('role-breakdown');
        const entries = Object.entries(breakdown);
        target.innerHTML = entries.length ? '' : '<div class="text-muted">No role data yet.</div>';

        entries.forEach(([role, count]) => {
            const percent = total ? Math.round((count / total) * 100) : 0;
            target.insertAdjacentHTML('beforeend', `
                <div class="role-row">
                    <div class="role-row-header"><span>${roleLabel(role)}</span><strong>${count}</strong></div>
                    <div class="role-bar"><span style="width:${percent}%"></span></div>
                </div>
            `);
        });
    }

    function renderVisitors(visitors) {
        const tbody = document.getElementById('recent-visitors');
        tbody.innerHTML = visitors.length ? '' : '<tr><td colspan="7" class="text-muted">No visitors for this filter.</td></tr>';

        visitors.forEach((visitor) => {
            const pages = visitor.pages.slice(0, 4).map((page) => `<span class="page-chip">${page}</span>`).join('') || '<span class="text-muted">None</span>';
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><strong>${visitor.name}</strong> ${visitor.online ? '<span class="badge bg-success ms-1">Online</span>' : ''}</td>
                    <td>${visitor.role}</td>
                    <td>${visitor.visited_at}<div class="small text-muted">${visitor.last_seen || ''}</div></td>
                    <td>${visitor.duration}</td>
                    <td>${visitor.device}</td>
                    <td>${visitor.ip || ''}</td>
                    <td>${pages}</td>
                </tr>
            `);
        });
    }

    async function loadTraffic() {
        minutesControl.classList.toggle('d-none', rangeSelect.value !== 'minutes');
        const params = new URLSearchParams({
            range: rangeSelect.value,
            minutes: minutesInput.value || '30',
            role: roleSelect.value,
        });
        const response = await fetch(`${shell.dataset.trafficUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();

        document.getElementById('traffic-total').textContent = data.total_visitors;
        document.getElementById('traffic-online').textContent = data.online_count;
        document.getElementById('traffic-peak').textContent = `${data.peak.label} (${data.peak.visits})`;

        if (roleSelect.options.length === 1) {
            data.roles.forEach((role) => roleSelect.add(new Option(roleLabel(role), role)));
        }

        drawChart(data.series);
        renderRoles(data.role_breakdown, data.total_visitors);
        renderVisitors(data.recent_visitors);

        window.clearTimeout(refreshTimer);
        refreshTimer = window.setTimeout(loadTraffic, rangeSelect.value === 'live' ? 10000 : 60000);
    }

    [rangeSelect, minutesInput, roleSelect].forEach((control) => control.addEventListener('change', loadTraffic));
    window.addEventListener('resize', () => loadTraffic());
    loadTraffic();
});
</script>
@endsection
