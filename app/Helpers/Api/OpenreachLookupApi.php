<?php

namespace App\Helpers\Api;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenreachLookupApi
{
    private function request($endpoint, $options)
    {
        $default_options = [
            'headers' => [
                'Referer' => 'https://www.openreach.com/',
            ]
        ];

        $options += $default_options;

        try {
            $client = new Client(['base_uri' => 'https://api.openreach.com']);
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

        $cache_key = 'openreach_lookup_' . Str::slug($postcode);

        if (Cache::has($cache_key)) {
            Log::debug("Getting openreach lookup from cache for {$cache_key}");
            $response = Cache::get($cache_key);
        } else {
            Log::debug("Getting openreach lookup FRESH for {$cache_key}");

            $response = $this->request('/services/ecc', [
                'headers' => [
                    'Referer' => 'https://www.openreach.com/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
                'query' => [
                    'postcode' => $postcode,
                ]
            ]);

            Cache::put($cache_key, $response);
        }

        $addresses = [];
        if (!is_null($response) && array_key_exists("body", $response) && array_key_exists("addresses", $response["body"]) && !empty($response["body"]["addresses"])) {
            foreach ($response["body"]["addresses"] as $address) {
                $openReachAddress = new AddressLookup(
                    addressKey: $address["value"] ?? null,
                    type: $address["type"] ?? null,
                    uprn: $address["uprn"] ?? null,
                    districtCode: $address["district"] ?? null,
                    coordinatesEasting: $address["addressparts"]["CoordinatesEasting"] ?? null,
                    coordinatesNorthing: $address["addressparts"]["CoordinatesNorthing"] ?? null,
                    latitude: array_key_exists("Latitude", $address["addressparts"]) ? (float)$address["addressparts"]["Latitude"] : null,
                    longitude: array_key_exists("Longitude", $address["addressparts"]) ? (float)$address["addressparts"]["Longitude"] : null,
                    label: $address["label"] ?? null,
                    organisationName: $address["addressparts"]["OrganisationName"] ?? null,
                    subPremises: $address["addressparts"]["SubPremises"] ?? null,
                    premisesName: $address["addressparts"]["PremisesName"] ?? null,
                    dependentThoroughfareName: $address["addressparts"]["DependentThoroughfareName"] ?? null,
                    thoroughfareNumber: $address["addressparts"]["ThoroughfareNumber"] ?? null,
                    thoroughfareName: $address["addressparts"]["ThoroughfareName"] ?? null,
                    doubleDependentLocality: $address["addressparts"]["DoubleDependentLocality"] ?? null,
                    locality: $address["addressparts"]["Locality"] ?? null,
                    postTown: $address["addressparts"]["PostTown"] ?? null,
                    postCode: $address["addressparts"]["PostCode"] ?? null,
                    county: $address["addressparts"]["County"] ?? null,
                    country: $address["addressparts"]["Country"] ?? null,
                );

                $addresses[] = $openReachAddress;
            }
        }
        return $addresses;
    }
}
