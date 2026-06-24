<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\DashboardRoute;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Permissions granted by each application role.
     *
     * @var array<string, array<int, string>>
     */
    private array $rolePermissions = [
        'admin' => [
            'users.create', 'users.edit', 'users.delete', 'users.view', 'users.assign-roles',
            'classes.create', 'classes.edit', 'classes.delete', 'classes.view',
            'subjects.create', 'subjects.edit', 'subjects.delete', 'subjects.view',
            'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
            'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
            'payments.create', 'payments.edit', 'payments.view', 'payments.reports', 'overrides.create', 'overrides.view',
            'attendance.mark', 'attendance.view',
            'change-requests.approve', 'change-requests.view',
            'profile.edit', 'profile.view',
            'system.settings', 'system.reports',
        ],
        'hod' => [
            'classes.view', 'classes.edit',
            'subjects.view', 'subjects.edit',
            'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
            'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
            'overrides.create', 'overrides.view',
            'change-requests.approve', 'change-requests.view',
            'profile.edit', 'profile.view',
        ],
        'cbt_personnel' => [
            'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts', 'exams.monitor',
            'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
            'profile.edit', 'profile.view',
        ],
        'teacher' => [
            'classes.view',
            'subjects.view',
            'exams.create', 'exams.edit', 'exams.delete', 'exams.view', 'exams.live-toggle', 'exams.results-toggle', 'exams.ai-generate', 'exams.reset-attempts',
            'questions.create', 'questions.edit', 'questions.delete', 'questions.view',
            'attendance.mark', 'attendance.view',
            'profile.edit', 'profile.view',
        ],
        'prefect' => [
            'users.view',
            'users.edit',
            'profile.edit', 'profile.view',
        ],
        'student' => [
            'exams.take',
            'exams.view-results',
            'change-requests.create',
            'change-requests.view',
            'profile.edit', 'profile.view',
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        }

        $user = auth()->user();
        $rolePermissions = $this->rolePermissions[$user->role] ?? [];

        if (!in_array($permission, $rolePermissions, true) && !$user->hasPermissionTo($permission)) {
            return redirect()->route(DashboardRoute::forUser($user))
                ->with('info', 'You were redirected to your dashboard.');
        }

        return $next($request);
    }
}
