<?php

namespace App\Helpers\Api\Cloudaudit;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CloudAuditApiReport extends CloudAuditApi
{
    public function __construct() {}

    public function getReports()
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(config('services.cloudaudit.api_url').'subscriptionReport');

        return $res->json();
    }

    public function getReport($id)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)->get(config('services.cloudaudit.api_url').'subscriptionReport/'.$id);

        return $res->json();
    }

    public function getReportByAccount($accountId)
    {
        $accessToken = $this->tokenRequest();

        $res = Http::withToken($accessToken)
            ->withHeaders(['idempotencyKey' => Hash::make(now())])
            ->post(config('services.cloudaudit.api_url').'subscriptionReport',
                [
                    'accountId' => $accountId,
                ]
            );

        $data = $res->json();
        if (! $res->successful()) {
            return ['errors' => $data['detail']];
        }

        return $data;
    }
}
