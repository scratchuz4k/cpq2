<?php

namespace App\Helpers\Api;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\Api;
use App\Models\Quote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudMarketApi
{
    public function __construct() {}

    public static function getQuotes(Quote $quote, $connectivityType, CloudMarketAddress $address)
    {

        $url = env('CLOUDMARKET_CONNECTION_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com');
        $requestTimeout = 420;
        $apis = Api::active()->whereIn('code', CloudMarketSuppliersEnum::values())->get();

        $quoteAddress = [
            'ConnectivityType' => $connectivityType,
            'Address1.Postcode' => $address->postCode,
            'Address1.Latitude' => $address->lat,
            'Address1.Longitude' => $address->lon,

            'Address1.UPRN' => $address->uprn,
            'Address1.AddressKey' => $address->addressKey,
            'Address1.BuildingName' => $address->buildingName,
            'Address1.BuildingNumber' => $address->buildingNumber,
            'Address1.SubPremises' => $address->subPremises,
            'Address1.Street' => $address->street,
            'Address1.Town' => $address->town,

            'ManagedOption' => 0,
            'TermMonths' => [],
            'ConnectionSpeeds.MinBearer' => 100,
            'ConnectionSpeeds.MaxBearer' => 100000,
            'ConnectionSpeeds.MinSpeed' => 10,
            'ConnectionSpeeds.MaxSpeed' => 100000,
        ];

        if (array_key_exists('managed', $quote->configs)) {
            $quoteAddress['ManagedOption'] = (int) $quote->configs['managed'];
        }

        if (array_key_exists('contract_length', $quote->configs)) {
            $quoteAddress['TermMonths'] = (bool) $quote->configs['contract_length'] ? explode(',', $quote->configs['contract_length']) : [];
        }

        if (array_key_exists('bearer', $quote->configs)) {
            $quoteAddress['ConnectionSpeeds.MinBearer'] = (int) $quote->configs['bearer'];
        }

        if (array_key_exists('speed', $quote->configs)) {
            $quoteAddress['ConnectionSpeeds.MinSpeed'] = (int) $quote->configs['speed'];
        }

        Log::debug('CloudMarketApi::getQuotes lookup:', $quoteAddress);

        $quoteCredentials = [];
        foreach ($apis as $api) {
            $quoteCredentials += self::resolveApiAuth($api);
        }

        $quoteCredentials = array_filter($quoteCredentials, function ($value) {
            return ! empty($value);
        });

        $quoteRequestParams = array_merge($quoteAddress, $quoteCredentials);

        return Http::withOptions([
            'stream' => true,
            'timeout' => $requestTimeout,
            'connect_timeout' => 30,
        ])->timeout($requestTimeout)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                $url.'/PartnerQuoteService/GetQuotes',
                $quoteRequestParams
            );
    }

    private static function resolveApiAuth($api): array
    {
        $supplierKey = CloudMarketSuppliersEnum::tryFrom($api?->code)?->name;
        if (! $supplierKey) {
            return [];
        }

        $connectionBase = "SupplierApiConnections.{$supplierKey}_Connection";

        switch ($api?->auth_type) {
            case 'basic':
                return [
                    "{$connectionBase}.Username" => $api?->username ?? env(strtoupper("{$supplierKey}_CONNECTION_USERNAME")),
                    "{$connectionBase}.Password" => $api?->password ?? env(strtoupper("{$supplierKey}_CONNECTION_PASSWORD")),
                ];
            case 'basic_client':
                return [
                    "{$connectionBase}.Username" => $api?->username ?? env(strtoupper("{$supplierKey}_CONNECTION_USERNAME")),
                    "{$connectionBase}.Password" => $api?->password ?? env(strtoupper("{$supplierKey}_CONNECTION_PASSWORD")),
                    "{$connectionBase}.ClientId" => $api?->client_id ?? env(strtoupper("{$supplierKey}_CONNECTION_CLIENTID")),
                ];
            case 'token':
                return [
                    "{$connectionBase}.Token" => $api?->token ?? env(strtoupper("{$supplierKey}_CONNECTION_TOKEN")),
                ];
            default:
                return [];
        }
    }
}
