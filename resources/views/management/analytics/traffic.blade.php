@extends('layouts.admin')

@section('title', 'Traffic & Analytics')

@section('content')
<div class="traffic-shell" data-traffic-url="{{ route('traffic.data') }}">
    <div class="traffic-range-pills" aria-label="Traffic range quick filters">
        <button type="button" class="traffic-pill" data-range="live">Live</button>
        <button type="button" class="traffic-pill" data-range="minutes">Minutes</button>
        <button type="button" class="traffic-pill" data-range="hourly">Hourly</button>
        <button type="button" class="traffic-pill active" data-range="daily">Daily</button>
        <button type="button" class="traffic-pill" data-range="weekly">Weekly</button>
        <button type="button" class="traffic-pill" data-range="monthly">Monthly</button>
        <button type="button" class="traffic-pill" data-range="yearly">Yearly</button>
    </div>

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
                <div class="card-body chart-card-body">
                    <div class="traffic-chart-wrap">
                    <canvas id="traffic-chart" height="260"></canvas>
                        <div id="traffic-chart-tip" class="traffic-chart-tip d-none"></div>
                    </div>
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
            <div id="recent-visitors-cards" class="visitor-card-list d-lg-none"></div>
        </div>
    </div>
</div>

<style>
.traffic-shell {
    display: grid;
    gap: 18px;
    min-width: 0;
}

.traffic-range-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 2px;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}

.traffic-pill {
    border: 1px solid #d9e1ea;
    background: #fff;
    color: #0a1931;
    border-radius: 999px;
    min-height: 44px;
    padding: 0 16px;
    font-weight: 600;
    white-space: nowrap;
}

.traffic-pill.active {
    background: #0a1931;
    border-color: #0a1931;
    color: #fff;
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

.chart-card-body {
    min-width: 0;
}

.traffic-chart-wrap {
    position: relative;
    width: 100%;
    min-height: 280px;
    overflow: hidden;
}

#traffic-chart {
    display: block;
    width: 100%;
    max-width: 100%;
    height: 280px;
    touch-action: manipulation;
}

.traffic-chart-tip {
    position: absolute;
    z-index: 2;
    transform: translate(-50%, -100%);
    min-width: 96px;
    border-radius: 8px;
    padding: 7px 9px;
    background: #0a1931;
    color: #fff;
    font-size: .8rem;
    text-align: center;
    pointer-events: none;
    box-shadow: 0 6px 18px rgba(10, 25, 49, .2);
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

.visitor-card-list {
    display: none;
}

.visitor-card {
    border: 1px solid #e8edf3;
    border-radius: 8px;
    padding: 12px;
    background: #fff;
}

.visitor-card-title {
    color: #0a1931;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.visitor-card-meta,
.visitor-detail-grid {
    color: #6c757d;
    font-size: .88rem;
}

.visitor-detail-grid {
    display: grid;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #edf1f5;
}

.visitor-detail-grid span {
    display: block;
    color: #0a1931;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.visitor-detail-toggle {
    min-height: 44px;
}

@media (max-width: 768px) {
    .traffic-shell {
        gap: 12px;
    }

    .traffic-toolbar,
    .traffic-stats {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .traffic-toolbar .form-select,
    .traffic-toolbar .form-control {
        min-height: 44px;
        width: 100%;
    }

    .traffic-stat {
        padding: 12px;
    }

    .traffic-stat strong {
        font-size: 1.15rem;
    }

    .traffic-chart-wrap {
        min-height: 230px;
    }

    #traffic-chart {
        height: 230px;
    }

    .traffic-shell .row {
        --bs-gutter-x: 0;
    }

    .traffic-shell .card-body {
        padding: 12px;
    }

    .traffic-shell .table-responsive {
        display: none;
    }

    .visitor-card-list {
        display: grid;
        gap: 10px;
    }
}

@media (max-width: 414px) {
    .traffic-pill {
        min-height: 44px;
        padding: 0 13px;
        font-size: .9rem;
    }

    .traffic-chart-wrap {
        min-height: 210px;
    }

    #traffic-chart {
        height: 210px;
    }
}

@media (max-width: 320px) {
    .traffic-shell {
        gap: 10px;
    }

    .traffic-chart-wrap {
        min-height: 190px;
    }

    #traffic-chart {
        height: 190px;
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
    const rangePills = document.querySelectorAll('.traffic-pill');
    const chartTip = document.getElementById('traffic-chart-tip');
    let refreshTimer;
    let chartBars = [];
    let chartSeries = [];

    function roleLabel(role) {
        return role.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function drawChart(series) {
        chartSeries = series;
        chartBars = [];
        chartTip.classList.add('d-none');

        const wrap = canvas.parentElement;
        const width = Math.max(240, wrap.clientWidth);
        const height = parseInt(getComputedStyle(canvas).height, 10) || 260;
        canvas.width = width * window.devicePixelRatio;
        canvas.height = height * window.devicePixelRatio;
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
        ctx.clearRect(0, 0, width, height);

        document.getElementById('traffic-empty').classList.toggle('d-none', series.length > 0);
        if (!series.length) return;

        const isMobile = width <= 480;
        const padding = isMobile ? 26 : 34;
        const chartWidth = width - padding * 2;
        const chartHeight = height - padding * 2;
        const maxVisits = Math.max(...series.map((item) => item.visits), 1);
        const barGap = isMobile ? 5 : 8;
        const barWidth = Math.max(10, (chartWidth / series.length) - barGap);
        const labelEvery = isMobile ? Math.max(1, Math.ceil(series.length / 4)) : Math.max(1, Math.ceil(series.length / 8));

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
            chartBars.push({ x, y, width: barWidth, height: barHeight, item });

            if (index % labelEvery === 0 || index === series.length - 1) {
                ctx.fillStyle = '#6c757d';
                ctx.font = `${isMobile ? 10 : 11}px Segoe UI`;
                const label = isMobile && item.label.length > 7 ? item.label.slice(0, 7) : item.label;
                ctx.save();
                if (isMobile && series.length > 4) {
                    ctx.translate(x + Math.min(barWidth, 12), height - 8);
                    ctx.rotate(-Math.PI / 6);
                    ctx.fillText(label, 0, 0, 48);
                } else {
                    ctx.fillText(label, x, height - 10, Math.max(40, barWidth + barGap));
                }
                ctx.restore();
            }
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
        const cards = document.getElementById('recent-visitors-cards');
        tbody.innerHTML = visitors.length ? '' : '<tr><td colspan="7" class="text-muted">No visitors for this filter.</td></tr>';
        cards.innerHTML = visitors.length ? '' : '<div class="text-muted py-3">No visitors for this filter.</div>';

        visitors.forEach((visitor, index) => {
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
            cards.insertAdjacentHTML('beforeend', `
                <div class="visitor-card">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="visitor-card-title">${visitor.name}</div>
                            <div class="visitor-card-meta">${visitor.role} - ${visitor.visited_at}</div>
                        </div>
                        ${visitor.online ? '<span class="badge bg-success">Online</span>' : ''}
                    </div>
                    <button class="btn btn-light w-100 mt-3 visitor-detail-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#visitor-details-${index}">
                        Details
                    </button>
                    <div class="collapse visitor-detail-grid" id="visitor-details-${index}">
                        <div>Duration <span>${visitor.duration}</span></div>
                        <div>Device <span>${visitor.device}</span></div>
                        <div>IP <span>${visitor.ip || 'Unknown'}</span></div>
                        <div>Pages <span>${pages}</span></div>
                    </div>
                </div>
            `);
        });
    }

    function syncPills() {
        rangePills.forEach((pill) => {
            pill.classList.toggle('active', pill.dataset.range === rangeSelect.value);
        });
    }

    function showChartTip(clientX, clientY) {
        if (!chartBars.length) return;

        const rect = canvas.getBoundingClientRect();
        const x = clientX - rect.left;
        const y = clientY - rect.top;
        const nearest = chartBars
            .map((bar) => ({
                bar,
                distance: Math.abs((bar.x + bar.width / 2) - x) + Math.max(0, y < bar.y ? bar.y - y : y - (bar.y + bar.height)),
            }))
            .sort((a, b) => a.distance - b.distance)[0]?.bar;

        if (!nearest) return;

        chartTip.innerHTML = `<strong>${nearest.item.visits}</strong><br>${nearest.item.label}`;
        chartTip.style.left = `${Math.min(Math.max(nearest.x + nearest.width / 2, 52), rect.width - 52)}px`;
        chartTip.style.top = `${Math.max(nearest.y - 8, 44)}px`;
        chartTip.classList.remove('d-none');
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

        syncPills();
        drawChart(data.series);
        renderRoles(data.role_breakdown, data.total_visitors);
        renderVisitors(data.recent_visitors);

        window.clearTimeout(refreshTimer);
        refreshTimer = window.setTimeout(loadTraffic, rangeSelect.value === 'live' ? 10000 : 60000);
    }

    rangePills.forEach((pill) => {
        pill.addEventListener('click', () => {
            rangeSelect.value = pill.dataset.range;
            loadTraffic();
        });
    });
    [rangeSelect, minutesInput, roleSelect].forEach((control) => control.addEventListener('change', loadTraffic));
    canvas.addEventListener('click', (event) => showChartTip(event.clientX, event.clientY));
    canvas.addEventListener('touchstart', (event) => {
        if (event.touches[0]) {
            showChartTip(event.touches[0].clientX, event.touches[0].clientY);
        }
    }, { passive: true });
    window.addEventListener('resize', () => drawChart(chartSeries));
    loadTraffic();
});
</script>
@endsection
