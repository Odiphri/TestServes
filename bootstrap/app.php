<?php

use App\Http\Middleware\CheckSchoolFeatureAccess;
use App\Http\Middleware\EnsureCbtHost;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureSchoolOwner;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\PrepareCbtTenant;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackTraffic;
use App\Support\TestServesDomains;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );

        $middleware->web(append: [
            PrepareCbtTenant::class,
            TrackTraffic::class,
            SecurityHeaders::class,
        ]);

        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            PrepareCbtTenant::class,
        );

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'platform.admin' => EnsurePlatformAdmin::class,
            'school.owner' => EnsureSchoolOwner::class,
            'cbt.host' => EnsureCbtHost::class,
            'school.feature' => CheckSchoolFeatureAccess::class,
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
                ->to(TestServesDomains::schoolLoginUrl($request))
                ->with('status', 'Your session expired. Please log in again.');
        });
    })->create();
