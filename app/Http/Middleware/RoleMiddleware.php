<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();
        $allowedRoles = array_filter(array_map('trim', explode(',', $role)));

        $matchesRole = in_array($user->role, $allowedRoles, true)
            || collect($allowedRoles)->contains(fn (string $allowedRole) => $user->hasRole($allowedRole));

        if (! $matchesRole) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
