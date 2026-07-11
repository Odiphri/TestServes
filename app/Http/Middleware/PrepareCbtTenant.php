<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\TenantDatabaseManager;
use App\Support\TestServesDomains;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrepareCbtTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $slug = TestServesDomains::schoolSlugFromRequest($request);

        if (! $slug) {
            return $next($request);
        }

        $school = School::on('mysql')
            ->with(['branding', 'plan'])
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if (! $school) {
            return $next($request);
        }

        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        if ($school->tenant_database_created_at && app(TenantDatabaseManager::class)->databaseExists($school)) {
            app(TenantDatabaseManager::class)->activateExisting($school);
        }

        return $next($request);
    }
}
