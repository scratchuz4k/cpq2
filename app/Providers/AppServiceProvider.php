<?php

namespace App\Providers;

use App\Events\QuoteStatusEvent;
use App\Listeners\QuoteStatusRegistryListener;
use Illuminate\Support\Str;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Providers\CustomResourceRegistrar;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Saml2\Saml2ExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
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

        Event::listen(
            QuoteStatusEvent::class,
            QuoteStatusRegistryListener::class
        );


        Scramble::routes(function (Route $route) {
            $available = [
                'api/account',
                'api/contact',
            ];
            return Str::startsWith($route->uri, $available);
        });
    }
}
