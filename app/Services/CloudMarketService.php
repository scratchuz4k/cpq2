<?php

namespace App\Services;

use App\Enums\CloudMarketSuppliersEnum;
use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Files\CloudMarketFile;
use App\Jobs\CloudMarketConnectivityProducts;
use App\Models\Api;
use App\Repositories\CloudMarketRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CloudMarketService
{
    public function __construct(protected CloudMarketRepository $cloudMarketRepository) {}

    public function get($connectivityType, CloudMarketAddress $address, $filters = [])
    {
        if (! env('CLOUDMARKET_CONNECTION_LIVE_DATA')) {
            $mock_prices = storage_path('mocks/prices.json');
            if (! is_file($mock_prices)) {
                return [];
            }

            return $this->cloudMarketRepository->sendResponse(json_decode(file_get_contents($mock_prices), true), $filters);
        }

        $apis = array_map(fn ($case) => $case->value, CloudMarketSuppliersEnum::cases());
        $apiRecords = Api::whereIn('code', $apis)->get();
        $apiCodes = $apiRecords->pluck('code')->sort()->implode('');

        $filename = 'live_prices_'.$address->toString().'_'.md5($apiCodes).'.json';
        $path = storage_path('/app/products/');
        $file = new CloudMarketFile($path, $filename);
        $content = $file->getContent();
        $createdAt = Carbon::parse($content['created_at']);

        if (! count($apiRecords)) {
            $file->write([], true, now(), now(), [], [[
                'provider' => 'CPQ',
                'response' => 'No provider is configured',
            ]]);

            return $this->cloudMarketRepository->sendResponse($content, $filters);
        }

        if (request()->input('clearCache', false)) {
            Cache::forget($filename);
        }

        if (! Cache::has($filename)) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
        }

        if (! $file->exists() || (is_null($content['finished_at']) && $createdAt->addMinutes(10)->isPast())) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($file, $connectivityType, $address);
        }

        return $this->cloudMarketRepository->sendResponse($file->getContent(), $filters);
    }
}
