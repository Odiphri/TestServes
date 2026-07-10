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

        abort_unless($slug, 404);

        $school = School::query()
            ->with('branding')
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($school, 404);

        abort_unless($school->hasActiveSubscription(), 402);

        app(TenantDatabaseManager::class)->activate($school);

        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        return $next($request);
    }
}
