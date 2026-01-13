<?php

namespace App\Helpers\Api\Cloudaudit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class CloudAuditApi
{
    protected function tokenRequest()
    {
        if (Cache::has('cloudaudit-token')) {
            return Cache::get('cloudaudit-token');
        }

        $response = Http::asForm()->post(env('CLOUDAUDIT_API_AUTH'), [
            'grant_type' => 'client_credentials',
            'client_id' => env('CLOUDAUDIT_API_ID'),
            'client_secret' => env('CLOUDAUDIT_API_CLIENT'),
        ]);

        Cache::set('cloudaudit-token', $response->json()['access_token'], $response->json()['expires_in']);

        return $response->json()['access_token'];
    }
}
