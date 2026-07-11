<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\TenantDatabaseManager;
use App\Support\TestServesDomains;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCbtHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $slug = TestServesDomains::schoolSlugFromRequest($request);

        if (! $slug) {
            return response()->view('errors.school-portal-not-found', [], 404);
        }

        $school = School::query()
            ->with(['branding', 'plan'])
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if (! $school) {
            return response()->view('errors.school-portal-not-found', [], 404);
        }

        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        if (! $school->hasPortalAccess()) {
            return response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => $this->blockedReason($school),
            ], 402);
        }

        $tenants = app(TenantDatabaseManager::class);

        if (! $school->tenant_database_created_at || ! $tenants->databaseExists($school)) {
            return response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => 'setup_incomplete',
            ], 503);
        }

        $tenants->activateExisting($school);

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

        if ($school->status === 'trial' || $school->subscription_status === 'trial') {
            return 'trial_expired';
        }

        if ($school->status === 'expired' || $school->subscription_status === 'expired') {
            return 'subscription_expired';
        }

        return 'pending_payment';
    }
}
