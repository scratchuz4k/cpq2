<?php

namespace App\Providers;

use App\Helpers\Routing\ResourceRegistrar;
use App\Models\Token;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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

        Scramble::routes(function (Route $route) {
            if (! isset($route->action['as'])) {
                return false;
            }

            $action = explode('.', (string) $route->action['as']);

            if (! Str::startsWith($route->uri, 'api/') || count($action) < 3) {
                return false;
            }

            $model = str_replace('-', '_', Str::singular($action[1]));

            $ability = $this->abilities[$action[2]];

            return auth()->user()->can("$ability $model");
        });

        Sanctum::usePersonalAccessTokenModel(Token::class);
    }
}
