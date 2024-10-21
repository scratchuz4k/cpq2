<?php

use Sentry\Laravel\Integration;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\SetTenantByRequest;
use App\Http\Middleware\SetTenantBySession;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware(['api', 'auth:sanctum'])

                ->prefix('api')

                ->name('api.')

                ->group(base_path('routes/api.php'));

            Route::middleware('web')

                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetTenantBySession::class,
            NeedsTenant::class,
        ]);
        $middleware->api(append: [
            EnsureFrontendRequestsAreStateful::class,
            SubstituteBindings::class,
            SetTenantByRequest::class,
            // NeedsTenant::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'login/sso/callback'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
