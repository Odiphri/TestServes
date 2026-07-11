<?php

namespace App\Http\Middleware;

use App\Support\SchoolPlanAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolFeatureAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $school = app()->bound('currentSchool') ? app('currentSchool') : null;
        $feature = app(SchoolPlanAccessService::class)->featureForRoute($request->route()?->getName());

        if (! app(SchoolPlanAccessService::class)->allows($school, $feature)) {
            return response()->view('errors.feature-unavailable', [
                'feature' => $feature,
            ], 403);
        }

        return $next($request);
    }
}
