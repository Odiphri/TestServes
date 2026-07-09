<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Support\TestServesDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
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
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
            'school.owner' => \App\Http\Middleware\EnsureSchoolOwner::class,
            'cbt.host' => \App\Http\Middleware\EnsureCbtHost::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->to(TestServesDomains::schoolLoginUrl($request))
                ->with('status', 'Please log in to continue.');
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! in_array($exception->getStatusCode(), [402, 403, 412], true)) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getStatusCode() === 402 ? 'Payment required.' : 'Unauthorized.'], $exception->getStatusCode());
            }

            if ($exception->getStatusCode() === 402) {
                return response()->view('errors.402', [], 402);
            }

            return response()->view('errors.403', [], $exception->getStatusCode());
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
