<?php

namespace App\Helpers\Api;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\Api;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudMarketApi
{
    public function __construct() {}

    public static function getQuotes($connectivityType, CloudMarketAddress $address)
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
        ];

        Log::debug('CloudMarketApi::getQuotes lookup:', $quoteAddress);

        $quoteCredentials = [];
        foreach ($apis as $api) {
            $quoteCredentials += self::resolveApiAuth($api);
        }

        $quoteCredentials = array_filter($quoteCredentials, function ($value) {
            return ! empty($value);
        });

        $quoteRequestParams = array_merge($quoteAddress, $quoteCredentials);

        return Http::withOptions(['stream' => true])->timeout($requestTimeout)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                $url.'/PartnerQuoteService/GetQuotes',
                $quoteRequestParams
            );
    }

    private static function resolveApiAuth($api): array
    {
        $array = [
            CloudMarketSuppliersEnum::ITS->value => [
                'SupplierApiConnections.Its_Connection.Token' => $api?->token ?? env('ITS_CONNECTION_ACCESSKEY'),
            ],
            CloudMarketSuppliersEnum::Virtual1->value => [
                'SupplierApiConnections.Virtual1_Connection.Username' => $api?->username ?? env('VIRTUAL1_CONNECTION_USERNAME'),
                'SupplierApiConnections.Virtual1_Connection.Password' => $api?->password ?? env('VIRTUAL1_CONNECTION_PASSWORD'),
            ],
            CloudMarketSuppliersEnum::Gamma->value => [
                'SupplierApiConnections.Gamma_Connection.Username' => $api?->username ?? env('GAMMA_CONNECTION_USERNAME'),
                'SupplierApiConnections.Gamma_Connection.Password' => $api?->password ?? env('GAMMA_CONNECTION_PASSWORD'),
                'SupplierApiConnections.Gamma_Connection.ClientId' => $api?->client_id ?? env('GAMMA_CONNECTION_CLIENTID'),
            ],
            CloudMarketSuppliersEnum::Giacom->value => [
                'SupplierApiConnections.Giacom_Connection.Username' => $api?->username ?? env('GIACOM_CONNECTION_USERNAME'),
                'SupplierApiConnections.Giacom_Connection.Password' => $api?->password ?? env('GIACOM_CONNECTION_PASSWORD'),
            ],
            CloudMarketSuppliersEnum::Vorboss->value => [
                'SupplierApiConnections.Vorboss_Connection.Token' => $api?->token ?? env('VORBOSS_CONNECTION_ACCESSKEY'),
            ],
        ];

        return $array[$api->code];
    }
}
