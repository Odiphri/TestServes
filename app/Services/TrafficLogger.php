<?php

namespace App\Services;

use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Http\Request;

class TrafficLogger
{
    public function start(Request $request, User $user): TrafficLog
    {
        $log = TrafficLog::create([
            'session_id' => $request->session()->getId(),
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'role' => $user->role,
            'ip_address' => $request->ip(),
            'device_type' => $this->deviceType((string) $request->userAgent()),
            'browser' => $this->browser((string) $request->userAgent()),
            'user_agent' => $request->userAgent(),
            'pages_visited' => [],
            'login_at' => now(),
            'last_activity_at' => now(),
        ]);

        $request->session()->put('traffic_log_id', $log->id);

        return $log;
    }

    public function touch(Request $request): void
    {
        $user = $request->user();

        if (!$user || !$request->hasSession()) {
            return;
        }

        $log = $this->currentLog($request, $user);

        if (!$log || $log->logout_at) {
            return;
        }

        $pages = collect($log->pages_visited ?: []);
        $path = '/' . ltrim($request->path(), '/');

        if (!$pages->last() || ($pages->last()['path'] ?? null) !== $path) {
            $pages->push([
                'path' => $path,
                'route' => $request->route()?->getName(),
                'visited_at' => now()->toDateTimeString(),
            ]);
        }

        $log->update([
            'last_activity_at' => now(),
            'duration_seconds' => max(0, $log->login_at->diffInSeconds(now())),
            'pages_visited' => $pages->take(-100)->values()->all(),
        ]);
    }

    public function end(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $logId = $request->session()->get('traffic_log_id');

        if (!$logId) {
            return;
        }

        $log = TrafficLog::find($logId);

        if (!$log || $log->logout_at) {
            return;
        }

        $endedAt = now();

        $log->update([
            'last_activity_at' => $endedAt,
            'logout_at' => $endedAt,
            'duration_seconds' => max(0, $log->login_at->diffInSeconds($endedAt)),
        ]);

        $request->session()->forget('traffic_log_id');
    }

    private function currentLog(Request $request, User $user): ?TrafficLog
    {
        $logId = $request->session()->get('traffic_log_id');

        if ($logId) {
            return TrafficLog::whereKey($logId)->where('user_id', $user->id)->first();
        }

        return $this->start($request, $user);
    }

    private function deviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'Tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    private function browser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Unknown',
        };
    }
}
