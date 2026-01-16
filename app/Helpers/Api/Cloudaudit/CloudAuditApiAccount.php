<?php

namespace App\Helpers\Api\Cloudaudit;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CloudAuditApiAccount extends CloudAuditApi
{
    public function __construct() {}

    public function getAccounts()
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(config('external_sources.cloudaudit.api_url').'cloudAccount');

        return $res->json();
    }

    public function getAccount($id)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(config('external_sources.cloudaudit.api_url').'cloudAccount/'.$id);

        return $res;
    }

    public function storeAccount($code, $name)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)
            ->withHeaders(['idempotencyKey' => Hash::make(now())])
            ->post(config('external_sources.cloudaudit.api_url').'cloudAccount', [
                'accountExternalId' => $code,
                'accountAlias' => $name,

            ]);
        $data = $res->json();
        if (! $res->successful()) {
            return ['errors' => $data['detail']];
        }

        return $data;
    }
}
