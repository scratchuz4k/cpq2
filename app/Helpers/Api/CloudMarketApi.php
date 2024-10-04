<?php

namespace App\Helpers\Api;

use Illuminate\Support\Facades\Storage;
use App\Jobs\CloudMarketConnectivityProducts;
use Illuminate\Pagination\LengthAwarePaginator;

class CloudMarketApi
{
    public function __construct() {}

    public function get($connectivityType, CloudMarketAddress $address)
    {
        if (env('PARTNER_QUOTE_SERVICE_ENDPOINT') == 'mock') {
            $mock_prices = storage_path('mocks/prices.json');
            if (!is_file($mock_prices)) {
                return [];
            }
            return json_decode(file_get_contents($mock_prices), true);
        }
        $filename = "/app/public/connectivity/" . $address->toString() . '.json';

        if (!Storage::exists($filename)) {
            CloudMarketConnectivityProducts::dispatch($filename, $connectivityType, $address);
        }

        $fileContent = json_decode(file_get_contents(storage_path($filename)), true);
        $items = collect($fileContent['data']);

        // Manually paginate
        $perPage =  request()->input('perPage', 10);
        $currentPage = request()->input('page', 1);
        $items = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return [
            "data" => $items,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => count($fileContent['data']),
            'finished_at' =>  $fileContent['finished_at'],
            'started_at' =>  $fileContent['started_at']
        ];
    }
}
// current_page
// data
// per_page
// total
