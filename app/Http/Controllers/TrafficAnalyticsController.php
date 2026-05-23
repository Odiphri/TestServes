<?php

namespace App\Http\Controllers;

use App\Models\TrafficLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class TrafficAnalyticsController extends Controller
{
    private array $allowedRoles = ['admin', 'hod', 'cbt_personnel'];

    public function index(Request $request)
    {
        $this->authorizeTraffic($request);

        return view('management.analytics.traffic');
    }

    public function data(Request $request)
    {
        $this->authorizeTraffic($request);

        $validated = $request->validate([
            'range' => ['nullable', 'in:live,minutes,hourly,daily,weekly,monthly,yearly'],
            'minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $range = $validated['range'] ?? 'daily';
        $now = now();
        [$start, $bucket] = $this->rangeStartAndBucket($range, (int) ($validated['minutes'] ?? 30), $now);

        $logs = TrafficLog::query()
            ->when($range !== 'live', fn ($query) => $query->where('login_at', '>=', $start))
            ->when($range === 'live', fn ($query) => $query
                ->whereNull('logout_at')
                ->where('last_activity_at', '>=', $now->copy()->subMinutes(5)))
            ->when(!empty($validated['role']), fn ($query) => $query->where('role', $validated['role']))
            ->latest('login_at')
            ->get();

        $series = $this->series($logs, $range, $bucket);
        $peak = collect($series)->sortByDesc('visits')->first();

        return response()->json([
            'range' => $range,
            'total_visitors' => $logs->count(),
            'online_count' => TrafficLog::whereNull('logout_at')
                ->where('last_activity_at', '>=', $now->copy()->subMinutes(5))
                ->count(),
            'role_breakdown' => $logs
                ->groupBy('role')
                ->map(fn ($roleLogs) => $roleLogs->count())
                ->sortDesc()
                ->all(),
            'series' => $series,
            'peak' => $peak ?: ['label' => 'No activity yet', 'visits' => 0],
            'recent_visitors' => $logs->take(20)->map(fn (TrafficLog $log) => [
                'name' => $log->user_name ?: 'Unknown user',
                'role' => ucwords(str_replace('_', ' ', (string) $log->role)),
                'visited_at' => $log->login_at?->format('M j, Y g:ia'),
                'last_seen' => $log->last_activity_at?->diffForHumans(),
                'duration' => $this->duration($log->duration_seconds),
                'device' => trim(($log->device_type ?: 'Unknown') . ' / ' . ($log->browser ?: 'Unknown')),
                'ip' => $log->ip_address,
                'pages' => collect($log->pages_visited ?: [])->pluck('path')->unique()->values()->all(),
                'online' => !$log->logout_at && $log->last_activity_at && $log->last_activity_at->gte($now->copy()->subMinutes(5)),
            ])->values(),
            'roles' => TrafficLog::query()->select('role')->distinct()->whereNotNull('role')->pluck('role')->values(),
        ]);
    }

    private function authorizeTraffic(Request $request): void
    {
        abort_unless($request->user() && in_array($request->user()->role, $this->allowedRoles, true), 403);
    }

    private function rangeStartAndBucket(string $range, int $minutes, $now): array
    {
        return match ($range) {
            'live' => [$now->copy()->subMinutes(5), 'minute'],
            'minutes' => [$now->copy()->subMinutes($minutes), 'minute'],
            'hourly' => [$now->copy()->startOfDay(), 'hour'],
            'weekly' => [$now->copy()->subWeeks(12)->startOfWeek(), 'week'],
            'monthly' => [$now->copy()->subMonths(12)->startOfMonth(), 'month'],
            'yearly' => [$now->copy()->subYears(5)->startOfYear(), 'year'],
            default => [$now->copy()->subDays(30)->startOfDay(), 'day'],
        };
    }

    private function series($logs, string $range, string $bucket): array
    {
        if ($logs->isEmpty()) {
            return [];
        }

        return $logs
            ->groupBy(fn (TrafficLog $log) => $this->bucketKey($log->login_at, $bucket))
            ->map(fn ($bucketLogs, $key) => [
                'label' => $this->bucketLabel($bucketLogs->first()->login_at, $bucket),
                'visits' => $bucketLogs->count(),
                'sort' => $key,
            ])
            ->sortBy('sort')
            ->map(fn ($item) => [
                'label' => $item['label'],
                'visits' => $item['visits'],
            ])
            ->values()
            ->all();
    }

    private function bucketKey($date, string $bucket): string
    {
        $date = CarbonImmutable::parse($date);

        return match ($bucket) {
            'minute' => $date->format('Y-m-d H:i'),
            'hour' => $date->format('Y-m-d H'),
            'week' => $date->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            'year' => $date->format('Y'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel($date, string $bucket): string
    {
        $date = CarbonImmutable::parse($date);

        return match ($bucket) {
            'minute' => $date->format('g:ia'),
            'hour' => $date->format('ga'),
            'week' => 'Week of ' . $date->startOfWeek()->format('M j'),
            'month' => $date->format('M Y'),
            'year' => $date->format('Y'),
            default => $date->format('M j'),
        };
    }

    private function duration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }
}
