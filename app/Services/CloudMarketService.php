<?php

namespace App\Services;

use DateTime;
use Exception;
use JsonMachine\Items;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use GuzzleHttp\Psr7\StreamWrapper;
use App\Helpers\Api\CloudMarketApi;
use App\Helpers\Api\CloudMarketFile;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Api\CloudMarketAddress;
use App\Repositories\CloudMarketRepository;
use App\Jobs\CloudMarketConnectivityProducts;

class CloudMarketService
{

    public function __construct(protected CloudMarketRepository $cloudMarketRepository) {}


    public function get($connectivityType, CloudMarketAddress $address, $filters = [])
    {
        if (env('PARTNER_QUOTE_SERVICE_ENDPOINT') == 'mock') {
            $mock_prices = storage_path('mocks/prices.json');
            if (!is_file($mock_prices)) {
                return [];
            }
            return $this->cloudMarketRepository->sendResponse(json_decode(file_get_contents($mock_prices), true), $filters);
        }

        $path = storage_path("/app/products/");
        $filename = $address->toString() . '.json';
        $file = new CloudMarketFile($path, $filename);

        if (!Cache::has($filename)) {
            $file->write([], true, null, now());

            if (env('PARTNER_QUOTE_AS_JOB')) {
                CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
            } else {
                $this->getConnectivityProducts($file, $connectivityType, $address, $filters);
            }
        }

        $content = $file->getContent();
        $createdAt = Carbon::parse($content['created_at']);

        if (is_null($content['finished_at']) && $createdAt->addMinutes(15)->isPast()) {
            if (env('PARTNER_QUOTE_AS_JOB')) {
                CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
            } else {
                $this->getConnectivityProducts($file, $connectivityType, $address, $filters);
            }
        }

        return $this->cloudMarketRepository->sendResponse($file->getContent(), $filters);
    }

    private function getConnectivityProducts(CloudMarketFile $file, $connectivityType, $address, $filters)
    {
        $res = CloudMarketApi::getQuotes($connectivityType, $address);
        $started_at = now();

        $prices = [];
        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            if (empty($value->providerKey)) {
                continue;
            }

            $temp = (array)$value;
            $temp['code'] = '';
            foreach (array_keys($temp) as $itemKey) {
                if (!(is_string($temp[$itemKey]) || is_numeric($temp[$itemKey]))) {
                    continue;
                }
                $temp['code'] .= $temp[$itemKey];
            }
            $prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];
            $file->write($prices, false, null, $started_at);

            if (($key % 250) == 0) {
                echo json_encode($this->cloudMarketRepository->sendResponse($file->getContent(), $filters));
            }
        }

        $file->write($prices, false, now(), $started_at);
        echo json_encode($this->cloudMarketRepository->sendResponse($file->getContent(), $filters));
    }
}
