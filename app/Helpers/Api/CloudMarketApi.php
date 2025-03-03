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
        $virtual1 = Api::where('code', CloudMarketSuppliersEnum::Virtual1->value)->first();
        $gamma = Api::where('code', CloudMarketSuppliersEnum::Gamma->value)->first();
        $giacom = Api::where('code', CloudMarketSuppliersEnum::Giacom->value)->first();
        $its = Api::where('code', CloudMarketSuppliersEnum::ITS->value)->first();
        $vorboss = Api::where('code', CloudMarketSuppliersEnum::Vorboss->value)->first();

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

        $quoteCredentials = [
            'SupplierApiConnections.Its_Connection.Its_AccessKey' => $its?->token ?? env('ITS_CONNECTION_ACCESSKEY'),
            'SupplierApiConnections.Virtual1_Connection.Username' => $virtual1?->username ?? env('VIRTUAL1_CONNECTION_USERNAME'),
            'SupplierApiConnections.Virtual1_Connection.Password' => $virtual1?->password ?? env('VIRTUAL1_CONNECTION_PASSWORD'),
            'SupplierApiConnections.Gamma_Connection.Username' => $gamma?->username ?? env('GAMMA_CONNECTION_USERNAME'),
            'SupplierApiConnections.Gamma_Connection.Password' => $gamma?->password ?? env('GAMMA_CONNECTION_PASSWORD'),
            'SupplierApiConnections.Gamma_Connection.ClientId' => $gamma?->client_id ?? env('GAMMA_CONNECTION_CLIENTID'),
            'SupplierApiConnections.Giacom_Connection.Username' => $giacom?->username ?? env('GIACOM_CONNECTION_USERNAME'),
            'SupplierApiConnections.Giacom_Connection.Password' => $giacom?->password ?? env('GIACOM_CONNECTION_PASSWORD'),
            'SupplierApiConnections.Vorboss_Connection.BearerToken' => $vorboss?->token ?? env('VORBOSS_CONNECTION_ACCESSKEY'),
        ];

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
}
