<?php

namespace App\Listeners;

use App\Models\Activity;
use Illuminate\Auth\Events\Login;
use Stevebauman\Location\Facades\Location;

class RegisterUserLoginListener
{
    /**
     * Handle the Login event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip();
        $fields = [
            'ip',
            'countryName',
            'countryCode',
            'regionCode',
            'regionName',
            'cityName',
            'zipCode',
            'latitude',
            'longitude'
        ];

        Activity::create([
            'operation' => 'user_login',
            'activitable_id' => $user->id,
            'activitable_type' => $user::class,
            'data' => [
                'location' => array_intersect_key(
                    (array) Location::get($ip) ?? [],
                    array_flip($fields)),
                'user_agent' => request()->userAgent(),
            ],
            'user_id' => $user->id ?? null,
            'tenant_id' => $user->tenant_id ?? null,
        ]);
    }
}
