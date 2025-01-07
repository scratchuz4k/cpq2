<?php

namespace App\Helpers\Api;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class CloudMarketApi
{
    public function __construct() {}

    public static function getQuotes($connectivityType,  CloudMarketAddress $address)
    {

        $url =  env('CLOUDMARKET_CONNECTION_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com');
        $requestTimeout = 420;
        $virtual1 = ApiConfiguration::where('code', CloudMarketSuppliersEnum::Virtual1->value)->first();
        $gamma = ApiConfiguration::where('code', CloudMarketSuppliersEnum::Gamma->value)->first();
        $giacom = ApiConfiguration::where('code', CloudMarketSuppliersEnum::Giacom->value)->first();
        $its = ApiConfiguration::where('code', CloudMarketSuppliersEnum::ITS->value)->first();
        $vorboss = ApiConfiguration::where('code', CloudMarketSuppliersEnum::Vorboss->value)->first();

        $quoteAddress = [
            'ConnectivityType' => $connectivityType,
            'Address1.Postcode' => $address->postCode,
            'Address1.Latitude' => $address->lat,
            'Address1.Longitude' => $address->lon,

            "Address1.UPRN" =>  $address->uprn,
            "Address1.AddressKey" =>  $address->addressKey,
            "Address1.BuildingName" =>  $address->buildingName,
            "Address1.BuildingNumber" =>  $address->buildingNumber,
            "Address1.SubPremises" =>  $address->subPremises,
            "Address1.Street" =>  $address->street,
            "Address1.Town" =>  $address->town,

            "ManagedOption" => 0,
        ];

        Log::debug('CloudMarketApi::getQuotes lookup:', $quoteAddress);

        $quoteCredentials = [
            'SupplierApiConnections.Its_Connection.Its_AccessKey' => env('ITS_CONNECTION_ACCESSKEY', $its?->token),
            'SupplierApiConnections.Virtual1_Connection.Username' => env('VIRTUAL1_CONNECTION_USERNAME', $virtual1?->username),
            'SupplierApiConnections.Virtual1_Connection.Password' => env('VIRTUAL1_CONNECTION_PASSWORD', $virtual1?->password),
            'SupplierApiConnections.Gamma_Connection.Username' => env('GAMMA_CONNECTION_USERNAME', $gamma?->username),
            'SupplierApiConnections.Gamma_Connection.Password' => env('GAMMA_CONNECTION_PASSWORD', $gamma?->password),
            'SupplierApiConnections.Gamma_Connection.ClientId' => env('GAMMA_CONNECTION_CLIENTID', $gamma?->client_id),
            'SupplierApiConnections.Giacom_Connection.Username' => env('GIACOM_CONNECTION_USERNAME', $giacom?->username),
            'SupplierApiConnections.Giacom_Connection.Password' => env('GIACOM_CONNECTION_PASSWORD', $giacom?->password),
            'SupplierApiConnections.Vorboss_Connection.BearerToken' => env('VORBOSS_CONNECTION_ACCESSKEY', $vorboss?->token),
        ];

        $quoteRequestParams = array_merge($quoteAddress, $quoteCredentials);

        return Http::withOptions(['stream' => true])->timeout($requestTimeout)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                $url . '/PartnerQuoteService/GetQuotes',
                $quoteRequestParams
            );
    }
}
