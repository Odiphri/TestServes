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
        $school = app()->bound('currentSchool') ? app('currentSchool') : null;

        if ($school && ! $school->hasPortalAccess()) {
            return response()->view('errors.school-portal-blocked', [
                'school' => $school,
                'reason' => $this->blockedReason($school),
            ], 402);
        }

        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $feature = app(SchoolPlanAccessService::class)->featureForRoute($request->route()?->getName());

        if (! app(SchoolPlanAccessService::class)->allows($school, $feature)) {
            if ($request->route()?->getName() !== 'admin.dashboard') {
                abort(404);
            }

            return response()->view('errors.feature-unavailable', [
                'feature' => $feature,
            ], 403);
        }

        return $next($request);
    }

    private function blockedReason($school): string
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
}
