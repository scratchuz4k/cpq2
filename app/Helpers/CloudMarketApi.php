<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class CloudMarketApi
{

    public function __construct() {}

    public function get($connectivityType, $postCode)
    {
        $res = Http::timeout(360)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                env('PARTNER_QUOTE_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com') . '/PartnerQuoteService/' . env('PARTNER_QUOTE_SERVICE_ENDPOINT', 'TestNoParams'),
                [
                    'ConnectivityType' => $connectivityType,
                    'Address1.Postcode' => $postCode,

                    "ManagedOption" => 1,

                    'SupplierApiConnections.Its_Connection.Its_AccessKey' => env('ITS_CONNECTION_ITS_ACCESSKEY', '328736h87y78g823'),
                    'SupplierApiConnections.Virtual1_Connection.Username' => env('VIRTUAL1_CONNECTION_USERNAME', 'JenniferLawrence'),
                    'SupplierApiConnections.Virtual1_Connection.Password' => env('VIRTUAL1_CONNECTION_PASSWORD', 'secretpass'),
                    'SupplierApiConnections.Gamma_Connection.Username' => env('GAMMA_CONNECTION_USERNAME', 'AdamSandler'),
                    'SupplierApiConnections.Gamma_Connection.Password' => env('GAMMA_CONNECTION_PASSWORD', 'specialpass'),
                    'SupplierApiConnections.Gamma_Connection.ClientId' => env('GAMMA_CONNECTION_CLIENTID', '134213'),
                    'SupplierApiConnections.Giacom_Connection.Username' => env('GIACOM_CONNECTION_USERNAME', 'MasterWoo'),
                    'SupplierApiConnections.Giacom_Connection.Password' => env('GIACOM_CONNECTION_PASSWORD', '2345678'),
                ]
            );
        return $res->body();
    }
}
