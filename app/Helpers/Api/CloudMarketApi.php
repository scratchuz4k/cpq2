<?php

namespace App\Helpers\Api;

use App\Models\Price;
use JsonMachine\Items;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Facades\Http;

class CloudMarketApi
{
    public function __construct() {}

    public function get($connectivityType, CloudMarketAddress $address)
    {
        if (env('PARTNER_QUOTE_SERVICE_ENDPOINT') == 'mock') {
            $mock_prices = storage_path('mocks/prices.json');
            if (!is_file($mock_prices)) {
                return [];
            }
            return json_decode(file_get_contents($mock_prices), true);
        }

        $res = Http::withOptions(['stream' => true])->timeout(360)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                env('PARTNER_QUOTE_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com') . '/PartnerQuoteService/' . env('PARTNER_QUOTE_SERVICE_ENDPOINT', 'TestNoParams'),
                [
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


        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            echo json_encode($this->recalculatePrices($value));
        }
    }

    private function recalculatePrices($data)
    {
        if (empty($data->providerKey)) {
            return null;
        }

        $params = [
            'supplier' => $data->providerKey,
            'prices' => [
                'priceMonthly',
                'priceAnnual',
                'priceInstall',
            ],
            'data' => (array)$data,
        ];

        return Price::calculate($params);
    }
}
