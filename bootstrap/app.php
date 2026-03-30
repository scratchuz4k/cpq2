<?php

use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetTenantBySession;
use App\Http\Middleware\TenantPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Sentry\Laravel\Integration;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            TenantPermission::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetTenantBySession::class,
            NeedsTenant::class,
            CheckUserActive::class,
            // EnsureValidTenantSession::class
        ]);
        $middleware->api(append: [
            TenantPermission::class,
            EnsureFrontendRequestsAreStateful::class,
            SubstituteBindings::class,
            SetTenantBySession::class,
            NeedsTenant::class,
            CheckUserActive::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'auth/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {

            if (config('app.debug')) {
                return $response;
            }

            $status = $response->getStatusCode();

            $safeMessages = [
                419 => 'The page expired, please try again.',
                403 => 'You are not authorized to perform this action.',
                404 => 'The requested resource was not found.',
                500 => 'Something went wrong.',
                503 => 'Service unavailable, please try again later.',
            ];

            $message = $safeMessages[$status] ?? 'An unexpected error occurred.';

            if ($request->expectsJson()) {
                if (array_key_exists($status, $safeMessages)) {
                    return response()->json(['message' => $message], $status);
                }

                return $response;
            }

            if ($status === 419) {
                return back()->with('message', $message);
            }

            if (in_array($status, [403, 404, 500, 503])) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
