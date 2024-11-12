<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Api\OpenreachLookup;
use App\Helpers\Api\ShopBlocksApi;
use App\Helpers\Api\VirtualOneLookup;
use App\Helpers\FilterProvider;
use App\Http\Controllers\Controller;
use App\Services\CloudMarketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExternalController extends Controller
{
    public function __construct(protected CloudMarketService $cloudMarketService) {}
    /**
     * Request Openreach Api to get all address with a Postal Code.
     */
    public function getAddressesByPostCode(Request $request)
    {
        $request->validate([
            "postCode" => "required|min:4",
        ]);

        $virtualOneLookup = new VirtualOneLookup;
        $addressesByPostCode = $virtualOneLookup->findAddresses($request->postCode);
        $addresses = [];
        if (array_key_exists("address", $addressesByPostCode) && !empty($addressesByPostCode["address"])) {
            foreach ($addressesByPostCode["address"] as $address) {
                $labelAddress = "";
                $labelAddressParts = [];

                if (!empty($address["organisationName"])) {
                    $labelAddressParts[] = $address["organisationName"];
                }

                if (!empty($address["subPremises"])) {
                    $labelAddressParts[] = $address["subPremises"];
                }

                if (!empty($address["premisesName"])) {
                    $labelAddressParts[] = $address["premisesName"];
                }

                if (!empty($address["thoroughfareNumber"])) {
                    $labelAddressParts[] = $address["thoroughfareNumber"];
                }

                if (!empty($address["thoroughfareName"])) {
                    $labelAddressParts[] = $address["thoroughfareName"];
                }

                if (!empty($address["dependentThoroughfareName"])) {
                    $labelAddressParts[] = $address["dependentThoroughfareName"];
                }

                if (!empty($address["postTown"])) {
                    $labelAddressParts[] = $address["postTown"];
                }

                if (!empty($address["postcode"])) {
                    $labelAddressParts[] = $address["postcode"];
                }

                if (!empty($address["country"])) {
                    $labelAddressParts[] = $address["country"];
                }

                $labelAddress = implode(', ', $labelAddressParts);

                $addresses[] = [
                    "addressKey" => $address["addressReferenceNum"] ?? null,
                    "type" => $address["addressQualifier"] ?? null,
                    "uprn" => $address["uprn"] ?? null,
                    "districtCode" => $address["districtCode"] ?? null,
                    "coordinatesEasting" => $address["coordinatesEasting"] ?? null,
                    "coordinatesNorthing" => $address["coordinatesNorthing"] ?? null,
                    "latitude" => $address["latitude"] ?? null,
                    "longitude" => $address["longitude"] ?? null,
                    "label" => $labelAddress ?? null,
                    "organisationName" => $address["organisationName"] ?? null,
                    "subPremises" => $address["subPremises"] ?? null,
                    "premisesName" => $address["premisesName"] ?? null,
                    "dependentThoroughfareName" => $address["dependentThoroughfareName"] ?? null,
                    "thoroughfareNumber" => $address["thoroughfareNumber"] ?? null,
                    "thoroughfareName" => $address["thoroughfareName"] ?? null,
                    "postTown" => $address["postTown"] ?? null,
                    "postCode" => $address["postcode"] ?? null,
                    "country" => $address["country"] ?? null,
                ];
            }
        }

        if(empty($addresses)) {
            $openreachLookup = new OpenreachLookup;
            $addressesByPostCode = $openreachLookup->findAddresses($request->postCode);
            if (array_key_exists("body", $addressesByPostCode) && array_key_exists("addresses", $addressesByPostCode["body"]) && !empty($addressesByPostCode["body"]["addresses"])) {
                foreach ($addressesByPostCode["body"]["addresses"] as $address) {
                    $addresses[] = [
                        "addressKey" => $address["value"] ?? null,
                        "type" => $address["type"] ?? null,
                        "uprn" => $address["uprn"] ?? null,
                        "districtCode" => $address["district"] ?? null,
                        "coordinatesEasting" => $address["addressparts"]["CoordinatesEasting"] ?? null,
                        "coordinatesNorthing" => $address["addressparts"]["CoordinatesNorthing"] ?? null,
                        "latitude" => $address["addressparts"]["Latitude"] ?? null,
                        "longitude" => $address["addressparts"]["Longitude"] ?? null,
                        "label" => $labelAddress["label"] ?? null,
                        "organisationName" => $address["addressparts"]["OrganisationName"] ?? null,
                        "subPremises" => $address["addressparts"]["SubPremises"] ?? null,
                        "premisesName" => $address["addressparts"]["PremisesName"] ?? null,
                        "dependentThoroughfareName" => $address["addressparts"]["DependentThoroughfareName"] ?? null,
                        "thoroughfareNumber" => $address["addressparts"]["ThoroughfareNumber"] ?? null,
                        "thoroughfareName" => $address["addressparts"]["ThoroughfareName"] ?? null,
                        "postTown" => $address["addressparts"]["PostTown"] ?? null,
                        "postcode" => $address["addressparts"]["PostCode"] ?? null,
                        "country" => $address["addressparts"]["Country"] ?? null,
                    ];
                }
            } else {
                return;
            }
        }

        return collect($addresses)
            ->sortBy('label')
            ->values()
            ->all();
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
        return  Cache::remember($cache_key, 43200, function () use ($address) {
            $res = Http::get('https://api.tomtom.com/search/2/geocode/' . $address . '.json?key=' . env('TOMTOM_API_KEY') . '&countryCode=GB');
            $res = json_decode($res->body(), true);
            $position = [];
            if (is_null($res) ||  !array_key_exists('results', $res) || !count($res['results'])) {
                return $position;
            }

            $highestScoreIndex = -1;
            $highestScore = -1;

            foreach ($res['results'] as $index => $item) {
                if (isset($item['matchConfidence']['score']) && $item['matchConfidence']['score'] > $highestScore) {
                    $highestScore = $item['matchConfidence']['score'];
                    $highestScoreIndex = $index;
                }
            }
            $highestScoreIndex = $highestScoreIndex >= 0 ? $highestScoreIndex : 0;

            if (!empty($res['results'][$highestScoreIndex]['position'])) {
                $position = $res['results'][$highestScoreIndex]['position'];
            }

            return $position;
        });
    }

    /**
     * Request ShopBlock Api to get articles and services informations.
     */
    public function getArticlesFromCloudMarket(Request $request)
    {
        $request->validate([
            "connectivityType" => "required",
            "postCode" => "required",
            "uprn" => "nullable",
            "latitude" => "nullable",
            "longitude" => "nullable",
            "street" => "nullable",
            "town" => "nullable",
        ]);

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

        return $this->cloudMarketService->get($request->get('connectivityType'), $address, $request->input('filters', []));
    }

    public function getArticlesFromShopBlocks(Request $request)
    {
        $cache_key = md5("shop-blocks" . $request->input('category_id', 22));
        $res = Cache::remember($cache_key, 43200, function () use ($request) {
            $products = [];
            $page = 0;
            do {
                $page++;
                $category_id = $request->input('category_id', 22);
                $res = (new ShopBlocksApi())->get($category_id, 200, $page);
                if (!array_key_exists("data", $res)) {
                    break;
                }
                array_push($products, ...$res["data"]);
                $last_page = array_key_exists("meta", $res) ? $res["meta"]["last_page"] : 1;
            } while ($page != $last_page);

            $tempFilters = [
                "model" => [],
            ];

            foreach ($products as $key => $product) {
                $temp = '';
                foreach (array_keys($product) as $itemKey) {
                    if (!(is_string($product[$itemKey]) || is_numeric($product[$itemKey]))) {
                        continue;
                    }
                    $temp .= $product[$itemKey];
                }
                $model = (string) Str::of($product['name'])->words(3, '');
                $products[$key]['code'] = md5(Str::slug($temp));
                $products[$key]['price'] = (float)$product['price'];
                $products[$key]['model'] = $model;
                $products[$key]['url'] = 'https://hardware.cloud.market/smartphones/' . $product['slug'];
                $tempFilters = FilterProvider::process($tempFilters, $products[$key], true);
            }

            return ['items' => $products, 'filters' => $tempFilters];
        });

        $items = FilterProvider::get($res['items'], request()->input('filters', []));
        $meta = FilterProvider::paginate($items);
        return [...$meta, 'filters' => $res['filters']];
    }
}
