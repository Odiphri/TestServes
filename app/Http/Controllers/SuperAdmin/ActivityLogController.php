<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PlatformAdmin;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('actor')
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', '%'.$request->action.'%'))
            ->when($request->filled('platform_admin_id'), fn ($query) => $query->where('platform_admin_id', $request->platform_admin_id))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.activity-logs.index', [
            'logs' => $logs,
            'admins' => PlatformAdmin::orderBy('name')->get(),
        ]);
    }
}
