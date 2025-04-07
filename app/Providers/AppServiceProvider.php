<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('saml2', \SocialiteProviders\Saml2\Provider::class);
        });

        Scramble::routes(function (Route $route) {
            if (!isset($route->action['as']))
                return false;

            $action = explode( '.', $route->action['as']);

            if (!Str::startsWith($route->uri, 'api/') || count($action) < 3)
                return false;

            $model = str_replace('-', '_', Str::singular($action[1]));

            $ability = $this->abilities[$action[2]];
            return auth()->user()->can("$ability $model");
        });
    }
}
