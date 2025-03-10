<?php

namespace App\Helpers\Api;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoqateLookupApi
{
    private function request($endpoint, $options)
    {
        $default_options = [
            'headers' => [
                'Referer' => 'https://www.royalmail.com/user/register/personal?destination=%2Fpersonal%2Freceiving-mail%2Fredirection',
            ],
        ];

        $options += $default_options;

        try {
            $client = new Client(['base_uri' => 'https://services.postcodeanywhere.co.uk']);
            $response = $client->get($endpoint, $options);
            $json = $response->getBody()->getContents();

            if ($json) {
                $contents = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $contents['Items'] ?? ['error' => 'response could not be decoded'];
                }
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function normalisePostcode($postcode)
    {
        $postcode = strtoupper(trim($postcode));
        $postcode = str_replace(' ', '', $postcode);

        $regex = "/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z]))))\s?[0-9][A-Za-z]{2})$/";
        $incode = null;
        $outcode = null;

        if (preg_match($regex, $postcode)) {
            $incode = substr($postcode, -3);
            $outcode = str_replace($incode, '', $postcode);
        } else {
            // attempt Republic of Ireland validation if UK hasn't
            $regex = '/^((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W))[ -]?([0-9A-FHKNPRTV-Y]{4})$/';
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

    public function findAddresses($string, $container = null)
    {
        $cache_key = 'postcode_lookup_'.Str::slug($string).'_'.(! empty($container) ? Str::slug($container) : 'default');

        if (Cache::has($cache_key)) {
            Log::debug("Getting postcode lookup from cache for {$cache_key}");

            return Cache::get($cache_key);
        } else {
            Log::debug("Getting postcode lookup FRESH for {$cache_key}");
            $parsedPostcode = $this->normalisePostcode($string);

            if ($parsedPostcode['incode'] && $parsedPostcode['outcode']) {
                if (! $container) {
                    $container = urlencode("GB|RM|ENG|{$parsedPostcode['incode']}-{$parsedPostcode['outcode']}");
                }
            }

            if ($container) {
                $license_key = env('LOQATE_ADDRESS_KEY');

                $response = $this->request('/Capture/Interactive/Find/v1.00/json3ex.ws', [
                    'query' => [
                        'Key' => $license_key,
                        'Text' => $string,
                        'Origin' => 'GBR',
                        'Language' => 'en',
                        'Container' => $container,
                        'Filter' => 'undefined',
                        'Instance' => 'null',
                        'Test' => 'false',
                        '$block' => 'true',
                        '$cache' => 'true',
                    ],
                ]);

                Cache::put($cache_key, $response);

                return $response;
            }

            return ['error' => 'postcode not recognised'];
        }
    }

    public function getAddress($id)
    {
        $cache_key = 'postcode_getaddress_'.Str::slug($id);

        if (Cache::has($cache_key)) {
            Log::debug("Getting postcode address from cache for {$cache_key}");

            return Cache::get($cache_key);
        } else {
            Log::debug("Getting postcode address FRESH for {$cache_key}");
            if ($id) {
                $license_key = env('LOQATE_ADDRESS_KEY');
                $id = urlencode($id);

                $response = $this->request('/Capture/Interactive/Retrieve/v1.10/json3ex.ws', [
                    'query' => [
                        'Key' => $license_key,
                        'Id' => $id,
                        '$cache' => 'true',
                    ],
                ]);

                Cache::put($cache_key, $response);

                return $response;
            }

            return ['error' => 'no ID provided'];
        }
    }
}
