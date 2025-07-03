<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Api\OpenreachLookupApi;
use App\Helpers\Api\VirtualOneLookupApi;
use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\External\CloudMarketService;
use App\Services\External\ShopBlocksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExternalController extends Controller
{
    public function __construct(protected CloudMarketService $cloudMarketService, protected ShopBlocksService $shopBlocksService) {}

    /**
     * Request Openreach Api to get all address with a Postal Code.
     */
    public function getAddressesByPostCode(Request $request)
    {
        $request->validate([
            'postCode' => 'required|min:4',
        ]);

        $virtualOneLookup = new VirtualOneLookupApi;
        $addressesByPostCode = $virtualOneLookup->findAddresses($request->postCode);

        if (empty($addressesByPostCode)) {
            return;
            /*
            $openreachLookup = new OpenreachLookupApi;
            $addressesByPostCode = $openreachLookup->findAddresses($request->postCode);
            if (empty($addressesByPostCode)) {
                return;
            }
            */
        }

        return collect($addressesByPostCode)
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
            'fullAddress' => 'required',
        ]);

        if (! ($address = trim(preg_replace('/[\s]+/', ' ', $request->input('fullAddress'))))) {
            return response('Something went wrong', 400);
        }

        $cache_key = 'address_lookup_'.Str::slug($address);

        return Cache::remember($cache_key, 43200, function () use ($address) {
            $res = Http::get('https://api.tomtom.com/search/2/geocode/'.$address.'.json?key='.env('TOMTOM_API_KEY').'&countryCode=GB');
            $res = json_decode($res->body(), true);
            $position = [];
            if (is_null($res) || ! array_key_exists('results', $res) || ! count($res['results'])) {
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

            if (! empty($res['results'][$highestScoreIndex]['position'])) {
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
            'connectivityType' => 'required',
            'quote_id' => 'required|exists:quotes,id',
            'postCode' => 'required',
            'uprn' => 'nullable',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'street' => 'nullable',
            'town' => 'nullable',
        ]);

        $quote = Quote::find($request->get('quote_id'));

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

        return $this->cloudMarketService->get($quote, $request->get('connectivityType'), $address, $request->input('filters', []));
    }
}
