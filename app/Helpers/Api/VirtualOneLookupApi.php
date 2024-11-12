<?php

namespace App\Helpers\Api;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VirtualOneLookupApi
{
    private function request($endpoint, $options)
    {
        $authentication = [
            'auth' => [env('VIRTUAL1_CONNECTION_USERNAME', 'JenniferLawrence'), env('VIRTUAL1_CONNECTION_PASSWORD', 'secretpass')]
        ];

        $options += $authentication;

        try {
            $client = new Client(['base_uri' => 'https://api.virtual1.com']);
            $response = $client->get($endpoint, $options);
            $json = $response->getBody()->getContents();

            if (!$json) {
                throw new \Exception('No data Found!');
            }

            return json_decode($json, true);

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function normalisePostcode($postcode)
    {
        $postcode = strtoupper(trim(urldecode($postcode)));
        $postcode = str_replace(' ', '', $postcode);

        $regex = "/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z]))))\s?[0-9][A-Za-z]{2})$/";
        $incode = null;
        $outcode = null;

        if (preg_match($regex, $postcode)) {
            $incode = substr($postcode, -3);
            $outcode = str_replace($incode, '', $postcode);
        } else {
            // attempt Republic of Ireland validation if UK hasn't
            $regex = "/^((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W))[ -]?([0-9A-FHKNPRTV-Y]{4})$/";
            if (preg_match($regex, $postcode, $match)) {
                $outcode = $match[1];
                $incode = $match[2];
            }
        }

        return [
            'outcode' => $outcode,
            'incode' => $incode,
        ];
    }

    public function findAddresses($string)
    {
        $parsedPostcode = $this->normalisePostcode($string);
        $postcode = $parsedPostcode['outcode'] . $parsedPostcode['incode'];

        $cache_key = 'virtual1_address_lookup_' . Str::slug($postcode);

        if (Cache::has($cache_key)) {
            Log::debug("Getting virtual1 address lookup from cache for {$cache_key}");
            $response = Cache::get($cache_key);
        } else {
            Log::debug("Getting virtual1 address lookup FRESH for {$cache_key}");

            $response = $this->request('/utility/address-lookup', [
                'query' => [
                    'postcode' => $postcode,
                ]
            ]);

            Cache::put($cache_key, $response);
        }

        $addresses = [];
        if (array_key_exists("address", $response) && !empty($response["address"])) {
            foreach ($response["address"] as $address) {

                $labelAddress = "";
                $labelAddressParts = [];

                $addressKeys = [
                    "organisationName",
                    "subPremises",
                    "premisesName",
                    "thoroughfareNumber",
                    "thoroughfareName",
                    "dependentThoroughfareName",
                    "postTown",
                    "postcode",
                    "country",
                ];
                $labelAddressParts = array_filter(array_map(function($key) use ($address) {
                    return $address[$key] ?? null;
                }, $addressKeys));
                $labelAddress = implode(', ', $labelAddressParts);

                $addresses[] = [
                    'addressKey' => $address['addressReferenceNum'] ?? null,
                    'type' => $address["addressQualifier"] ?? null,
                    'uprn' => $address["uprn"] ?? null,
                    'districtCode' => $address["districtCode"] ?? null,
                    'coordinatesEasting' => $address["coordinatesEasting"] ?? null,
                    'coordinatesNorthing' => $address["coordinatesNorthing"] ?? null,
                    'latitude' => $address["latitude"] ?? null,
                    'longitude' => $address["longitude"] ?? null,
                    'label' => $labelAddress ?? null,
                    'organisationName' => $address["organisationName"] ?? null,
                    'subPremises' => $address["subPremises"] ?? null,
                    'premisesName' => $address["premisesName"] ?? null,
                    'dependentThoroughfareName' => $address["dependentThoroughfareName"] ?? null,
                    'thoroughfareNumber' => $address["thoroughfareNumber"] ?? null,
                    'thoroughfareName' => $address["thoroughfareName"] ?? null,
                    'postTown' => $address["postTown"] ?? null,
                    'postCode' => $address["postcode"] ?? null,
                    'country' => $address["country"] ?? null,
                ];
            }
        }

        return $addresses;
    }
}
