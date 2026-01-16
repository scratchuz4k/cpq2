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

        $response = Http::asForm()->post(config('external_sources.cloudaudit.api_auth'), [
            'grant_type' => 'client_credentials',
            'client_id' => config('external_sources.cloudaudit.api_id'),
            'client_secret' => config('external_sources.cloudaudit.api_client'),
        ]);

        Cache::set('cloudaudit-token', $response->json()['access_token'], $response->json()['expires_in']);

        return $response->json()['access_token'];
    }
}
