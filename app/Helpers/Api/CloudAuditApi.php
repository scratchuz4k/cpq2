<?php

namespace App\Helpers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudAuditApi
{
    private static int $requestTimeout = 420;

    public function __construct() {}

    public function createOrganization(string $code, string $name)
    {

        Log::debug('CloudMarketApi::getQuotes lookup:');

        return $this->postRequest('/PartnerQuoteService/GetQuotes', $params);
    }

    private function postRequest(string $endpoint, array $params)
    {
        $baseUrl = env('CLOUDMARKET_CONNECTION_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com');

        return Http::withOptions([
            'stream' => true,
            'timeout' => self::$requestTimeout,
            'connect_timeout' => 30,
        ])
            ->timeout(self::$requestTimeout)
            ->withToken(env('ALPNET_API_TOKEN'))
            ->get($baseUrl.$endpoint, $params);
    }
}
