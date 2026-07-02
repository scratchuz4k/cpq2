<?php

namespace App\Providers;

use App\Helpers\Routing\ResourceRegistrar;
use App\Models\Token;
use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    protected $abilities = [
        'index' => 'viewAny',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'destroy' => 'delete',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('saml2', \SocialiteProviders\Saml2\Provider::class);
        });

        $this->app->bind(\Illuminate\Routing\ResourceRegistrar::class, fn () => new ResourceRegistrar($this->app['router']));

        Sanctum::usePersonalAccessTokenModel(Token::class);

        Scramble::routes(function (Route $route) {
            $uri = $route->uri();

            // Exclude all routes that do not start with 'apis' from the documentation
            return ! str_starts_with($uri, 'apis');
        });

        RateLimiter::for('shopblocks-api', function () {
            return Limit::perMinute(50); // max API limit is 300
        });
    }
}
