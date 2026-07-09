<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next, ?string $section = null): Response
    {
        $admin = Auth::guard('platform_admin')->user();

        if (! $admin) {
            return redirect()->guest(route('platform.login'))
                ->with('status', 'Please log in to access the platform admin area.');
        }

        if (! $admin->is_active) {
            Auth::guard('platform_admin')->logout();

            return redirect()->route('platform.login')
                ->with('error', 'This platform admin account is inactive.');
        }

        if ($section && ! $admin->canAccessPlatformSection($section)) {
            abort(403);
        }

        return $next($request);
    }
}
