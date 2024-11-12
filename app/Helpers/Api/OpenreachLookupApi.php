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
        if (array_key_exists("body", $response) && array_key_exists("addresses", $response["body"]) && !empty($response["body"]["addresses"])) {
            foreach ($response["body"]["addresses"] as $address) {
                $addressParts = array_key_exists("addressparts", $address) ? $address["addressparts"] : [];
                $addresses[] = [
                    "addressKey" => array_key_exists("value", $address) ? $address["value"] : null,
                    "type" => array_key_exists("type", $address) ? $address["type"] : null,
                    "uprn" => array_key_exists("uprn", $address) ? $address["uprn"] : null,
                    "districtCode" => array_key_exists("district", $address) ? $address["district"] : null,
                    "label" => array_key_exists("label", $address) ? $address["label"] : null,
                    "coordinatesEasting" => array_key_exists("CoordinatesEasting", $addressParts) ? $addressParts["CoordinatesEasting"] : null,
                    "coordinatesNorthing" => array_key_exists("CoordinatesNorthing", $addressParts) ? $addressParts["CoordinatesNorthing"] : null,
                    "latitude" => array_key_exists("Latitude", $addressParts) ? $addressParts["Latitude"] : null,
                    "longitude" => array_key_exists("Longitude", $addressParts) ? $addressParts["Longitude"] : null,
                    "organisationName" => array_key_exists("OrganisationName", $addressParts) ? $addressParts["OrganisationName"] : null,
                    "subPremises" => array_key_exists("SubPremises", $addressParts) ? $addressParts["SubPremises"] : null,
                    "premisesName" => array_key_exists("PremisesName", $addressParts) ? $addressParts["PremisesName"] : null,
                    "dependentThoroughfareName" => array_key_exists("DependentThoroughfareName", $addressParts) ? $addressParts["DependentThoroughfareName"] : null,
                    "thoroughfareNumber" => array_key_exists("ThoroughfareNumber", $addressParts) ? $addressParts["ThoroughfareNumber"] : null,
                    "thoroughfareName" => array_key_exists("ThoroughfareName", $addressParts) ? $addressParts["ThoroughfareName"] : null,
                    "postTown" => array_key_exists("PostTown", $addressParts) ? $addressParts["PostTown"] : null,
                    "postCode" => array_key_exists("PostCode", $addressParts) ? $addressParts["PostCode"] : null,
                    "country" => array_key_exists("Country", $addressParts) ? $addressParts["Country"] : null,
                ];
            }
        }
        return $addresses;
    }
}
