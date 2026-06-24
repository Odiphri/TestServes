<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Support\DashboardRoute;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );

        $middleware->web(append: [
            \App\Http\Middleware\TrackTraffic::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            if ($request->user()) {
                return redirect()->route(DashboardRoute::forUser($request->user()))
                    ->with('info', 'You were redirected to your dashboard.');
            }

            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! in_array($exception->getStatusCode(), [403, 412], true)) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], $exception->getStatusCode());
            }

            if ($request->user()) {
                return redirect()->route(DashboardRoute::forUser($request->user()))
                    ->with('info', 'You were redirected to your dashboard.');
            }

            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->with('error', 'Your session expired. Please try again.');
        });
    })->create();
