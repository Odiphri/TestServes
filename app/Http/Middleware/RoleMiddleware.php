<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\DashboardRoute;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        }

        $user = auth()->user();
        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values();

        $matchesRole = $allowedRoles->contains($user->role)
            || collect($allowedRoles)->contains(fn (string $allowedRole) => $user->hasRole($allowedRole));

        if (! $matchesRole) {
            return redirect()->route(DashboardRoute::forUser($user))
                ->with('info', 'You were redirected to your dashboard.');
        }

        return $next($request);
    }
}
