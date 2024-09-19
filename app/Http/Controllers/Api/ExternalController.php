<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CloudMarketAddress;
use App\Helpers\ShopBlocksApi;
use App\Models\Price;
use App\Helpers\Geocoder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\CloudMarketApi;
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
            if (is_null($res) ||  !array_key_exists('results', $res) || !count($res['results'])) {
                return [];
            }
            return $res['results'][0]['position'];
        });
    }

    /**
     * Request ShopBlock Api to get articles and services informations.
     */
    public function getArticlesFromCloudMarket(Request $request)
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
            "uprn" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "street" => "required",
            "town" => "required",
        ]);

        $cache_key = md5("cloud-market-articles" . json_encode($request->all()));
        $res = Cache::remember($cache_key, 3600, function () use ($request) {
            $address = new CloudMarketAddress(
                postCode: $request->get('postCode'),
                uprn: $request->input('uprn', null),
                lat: $request->input('latitude', null),
                lon: $request->input('longitude', null),
                addressKey: $request->input('addressKey', null),
                buildingName: $request->input('buildingName', null),
                buildingNumber: $request->input('buildingNumber', null),
                subPremises: $request->input('subPremises', null),
                street: $request->input('street', null),
                town: $request->input('town', null),
            );

            $res =  (new CloudMarketApi())->get($request->get('connectivityType'), $address);
        });

        return $this->recalculatePrices($res);
    }

    public function getArticlesFromShopBlocks(Request $request)
    {
        $cache_key = md5("shop-blocks" . json_encode($request->all()));
        return Cache::remember($cache_key, 3600, function () use ($request) {
            $products = [];
            $page = 0;
            do {
                $page++;
                $res = (new ShopBlocksApi())->get(22, 200, $page);
                if (!array_key_exists("data", $res)) {
                    break;
                }
                array_push($products, ...$res["data"]);
                $last_page = array_key_exists("meta", $res) ? $res["meta"]["last_page"] : 1;
            } while ($page != $last_page);

            foreach ($products as $key => $product) {
                $model = (string) Str::of($product['name'])->words(3, '');
                $products[$key]['model'] = $model;
                $products[$key]['url'] = 'https://hardware.cloud.market/smartphones/' . $product['slug'];
                // $labels[$label] = !empty($labels[$label]) ? $labels[$label] + 1 : 1;
            }

            return $products;
        });
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
