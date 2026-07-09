<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PlatformActivity
{
    public static function log(string $action, string $description, ?Model $target = null): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        $request = request();
        $actor = Auth::guard('platform_admin')->user();

        ActivityLog::create([
            'platform_admin_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
