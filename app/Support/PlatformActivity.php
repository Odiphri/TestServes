<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PlatformActivity
{
    public static function log(string $action, string $description, ?Model $target = null, array $context = []): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        $request = request();
        $actor = Auth::guard('platform_admin')->user();

        $payload = [
            'platform_admin_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ];

        foreach ([
            'admin_role' => $actor?->role,
            'school_id' => $context['school_id'] ?? ($target instanceof \App\Models\School ? $target->id : null),
            'old_values' => $context['old_values'] ?? null,
            'new_values' => $context['new_values'] ?? null,
        ] as $column => $value) {
            if (Schema::hasColumn('activity_logs', $column)) {
                $payload[$column] = $value;
            }
        }

        ActivityLog::create($payload);
    }
}
