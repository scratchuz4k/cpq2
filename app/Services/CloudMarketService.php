<?php

namespace App\Services;

use DateTime;
use Exception;
use JsonMachine\Items;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\FilterProvider;
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
        if (!env('CLOUDMARKET_CONNECTION_LIVE_DATA')) {
            $mock_prices = storage_path('mocks/prices.json');
            if (!is_file($mock_prices)) {
                return [];
            }
            return $this->cloudMarketRepository->sendResponse(json_decode(file_get_contents($mock_prices), true), $filters);
        }

        $path = storage_path("/app/products/");
        $filename = $address->toString() . '.json';
        $file = new CloudMarketFile($path, $filename);

        if (request()->input("clearCache", false)) {
            Cache::forget($filename);
        }

        if (!Cache::has($filename)) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
        }

        $content = $file->getContent();
        $createdAt = Carbon::parse($content['created_at']);

        if (!$file->exists() || (is_null($content['finished_at']) && $createdAt->addMinutes(5)->isPast())) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
        }

        return $this->cloudMarketRepository->sendResponse($file->getContent(), $filters);
    }
}
