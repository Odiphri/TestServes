<?php

namespace App\Http\Middleware;

use App\Services\TrafficLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTraffic
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && !$request->expectsJson()) {
            app(TrafficLogger::class)->touch($request);
        }

        return $response;
    }
}
