<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('school_owner')->check()) {
            return redirect()->route('platform.login')
                ->with('status', 'Please log in to access your school owner dashboard.');
        }

        return $next($request);
    }
}
