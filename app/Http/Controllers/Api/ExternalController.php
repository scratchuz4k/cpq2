<?php

namespace App\Http\Controllers\Api;

use App\Models\Price;
use App\Helpers\Geocoder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\OpenreachLookup;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalController extends Controller
{
    /**
     * Request Openreach Api to get all address with a Postal Code.
     */
    public function getAddressesByPostCode(Request $request)
    {
        $request->validate([
            "postCode" => "required|min:4",
        ]);

        $openreachLookup = new OpenreachLookup;
        return $openreachLookup->findAddresses($request->postCode);
    }

    /**
     * Request TomTom Api to get adress information.
     */
    public function getPositionByAddress(Request $request)
    {
        $request->validate([
            "fullAddress" => "required"
        ]);

        if (!($address = trim(preg_replace('/[\s]+/', ' ', $request->input('fullAddress'))))) {
            return response("Something went wrong", 400);
        }

        $cache_key = 'address_lookup_' . Str::slug($address);
        return  Cache::remember($cache_key, 3600, function () use ($address) {
            $res = Http::get('https://api.tomtom.com/search/2/geocode/' . $address . '.json?key=' . env('TOMTOM_API_KEY') . '&countryCode=GB');
            $res = json_decode($res->body(), true);
            if (!array_key_exists('results', $res) || !count($res['results'])) {
                return [];
            }
            return $res['results'][0]['position'];
        });
    }

    /**
     * Request ShopBlock Api to get articles and services informations.
     */
    public function getArticlesFromShopBlock(Request $request)
    {
        if (env('PARTNER_QUOTE_SERVICE_ENDPOINT') == 'mock') {
            $mock_prices = storage_path('mocks/prices.json');
            if (is_file($mock_prices)) {
                $prices = json_decode(file_get_contents($mock_prices));
                return json_encode($this->recalculatePrices($prices));
            }
        }

        $request->validate([
            "connectivityType" => "required",
            "postCode" => "required",
            "latitude" => "required",
            "longitude" => "required",
        ]);

        $cache_key = md5(json_encode($request->all()));
        return Cache::remember($cache_key, 3600, function () use ($request) {
            $res = Http::timeout(360)->withToken(env('ALPNET_API_TOKEN'))
                ->get(
                    env('PARTNER_QUOTE_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com') . '/PartnerQuoteService/' . env('PARTNER_QUOTE_SERVICE_ENDPOINT', 'TestNoParams'),
                    [
                        'ConnectivityType' => $request->get('connectivityType'),
                        'Address1.Postcode' => $request->get('postCode'),
                        'Address1.Latitude' => $request->get('latitude'),
                        'Address1.Longitude' => $request->get('longitude'),

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
        });

        $prices = $this->recalculatePrices($res);
    }

    private function recalculatePrices($prices)
    {
        foreach ($prices as $key => $data) {

            if (empty($data->providerKey)) {
                continue;
            }

            $params = [
                'supplier' => $data->providerKey,
                'prices' => [
                    'priceMonthly',
                    'priceAnnual',
                    'priceInstall',
                ],
                'data' => json_decode(json_encode($data), 1),
            ];

            $prices[$key] = Price::calculate($params);
        }

        return $prices;
    }
}
