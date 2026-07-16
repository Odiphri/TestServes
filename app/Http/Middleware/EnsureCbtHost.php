<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\SubscriptionLifecycleService;
use App\Support\TenantDatabaseManager;
use App\Support\TestServesDomains;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCbtHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = TestServesDomains::schoolSlugFromRequest($request);

        if (app()->runningUnitTests() && ! $slug) {
            return $next($request);
        }

        if (! $slug) {
            return response()->view('errors.school-portal-not-found', [], 404);
        }

        $school = School::on('mysql')
            ->with(['branding', 'plan'])
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if (! $school) {
            return response()->view('errors.school-portal-not-found', [], 404);
        }

        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        $tenants = app(TenantDatabaseManager::class);
        $school = app(SubscriptionLifecycleService::class)->refresh($school);
        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        if (! $school->hasPortalAccess()) {
            $this->logoutCurrentPortalUser($request);

            return response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => $this->blockedReason($school),
            ], 402);
        }

        if (! $school->tenant_database_created_at || ! $tenants->databaseExists($school)) {
            return response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => 'setup_incomplete',
            ], 503);
        }

        $tenants->activateExisting($school);
        $this->ensurePortalSessionVersion($school, $request);

        return $next($request);
    }

    private function blockedReason(School $school): string
    {
        if ($school->status === 'suspended') {
            return 'suspended';
        }

        if ($school->status === 'deactivated') {
            return 'deactivated';
        }

        if ($school->status === 'expired' || $school->subscription_status === 'expired') {
            return 'subscription_expired';
        }

        return 'pending_payment';
    }

    private function ensurePortalSessionVersion(School $school, Request $request): void
    {
        $key = "portal_session_version.{$school->id}";
        $current = (int) $school->portal_session_version;

        if (! Auth::check()) {
            $request->session()->put($key, $current);

            return;
        }

        if ((int) $request->session()->get($key, $current) !== $current) {
            $this->logoutCurrentPortalUser($request);
            abort(response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => 'session_expired',
            ], 402));
        }

        $request->session()->put($key, $current);
    }

    private function logoutCurrentPortalUser(Request $request): void
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
