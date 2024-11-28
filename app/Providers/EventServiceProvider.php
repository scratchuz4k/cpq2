<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\OrderPlaced;
use App\Events\QuoteStatusEvent;
use App\Listeners\QuoteStatusRegistryListener;
use App\Listeners\SendOrderNotification;
use App\Listeners\TestListener;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        QuoteStatusEvent::class => [
            QuoteStatusRegistryListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

        // Additional custom event bindings can be placed here
    }

    public function shouldDiscoverEvents()
    {
        return false; // Disable automatic event discovery
    }
}
