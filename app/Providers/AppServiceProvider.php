<?php

namespace App\Providers;

use App\Helpers\Routing\ResourceRegistrar;
use App\Models\Token;
use App\Policies\ExportPolicy;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Sqids\Sqids;

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
        Gate::before(fn ($user, $ability) => $user->hasRole('Super Admin') ? true : null);

        Gate::policy('export', ExportPolicy::class);

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

        $this->app->singleton(Sqids::class, function () {
            return new Sqids(
                alphabet: 'M4tJov5idLjw6TySzYahfG2AWme1VuPEpX89K3rRgOUQFN0qnDxb7kHlCBcsIZ'
            );
        });
    }
}
