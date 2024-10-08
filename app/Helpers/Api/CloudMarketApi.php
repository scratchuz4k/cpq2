<?php

namespace App\Helpers\Api;

use App\Models\Product;
use Illuminate\Support\Str;
use App\Helpers\Prices\PriceRulesConnectivity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Jobs\CloudMarketConnectivityProducts;
use Illuminate\Pagination\LengthAwarePaginator;

class CloudMarketApi
{
    public function __construct() {}

    public function get($connectivityType, CloudMarketAddress $address, $filters = [])
    {
        if (env('PARTNER_QUOTE_SERVICE_ENDPOINT') == 'mock') {
            $mock_prices = storage_path('mocks/prices.json');
            if (!is_file($mock_prices)) {
                return [];
            }
            return json_decode(file_get_contents($mock_prices), true);
        }
        $path = storage_path("/app/products/");
        $filename =  $address->toString() . '.json';

        if (!Cache::has($filename)) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $starterData = json_encode([
                'wating_task' => true,
                'started_at' => now(),
                'finished_at' => null,
                'total' => 0,
                'data' => [],
            ]);

            Cache::put($filename, $starterData, 86400);
            if (env('APP_DEBUG')) {
                file_put_contents($path . $filename, $starterData);
            }

            CloudMarketConnectivityProducts::dispatch($path, $filename, $connectivityType, $address);
        }

        $fileContent = json_decode(Cache::get($filename), true);
        $filtersData = $this->processPricesndFilters($fileContent['data']);
        $items = $this->filterData($fileContent['data'], $filters);

        $order = request()->input('order', 'asc');
        $orderBy = request()->input('orderBy', 'productName');
        $items = $order == 'asc' ? $items->sortBy($orderBy) : $items->sortByDesc($orderBy);
        $perPage =  request()->input('perPage', 10);
        $currentPage = request()->input('page', 1);
        $temp = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return [
            "data" => $temp,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $items->count(),
            'finished_at' => isset($isUpdating) ? $isUpdating : $fileContent['finished_at'],
            'started_at' =>  $fileContent['started_at'],
            'wating_task' =>  $fileContent['wating_task'],
            'filters' =>  $filtersData,
        ];
    }

    private function processPricesndFilters(&$data)
    {
        $filters = [
            "providerName" => [],
            "connectivitySupplierName" => [],
            "contractTermMonths" => [],
            "bearer" => [],
            "speed" => []
        ];

        $priceRules = new PriceRulesConnectivity();
        foreach ($data as $key =>  $value) {
            foreach ($filters as $filterKey => $items) {
                if (!array_key_exists($filterKey, $value) || !$value[$filterKey]) {
                    continue;
                }

                if (in_array($value[$filterKey], $items)) {
                    continue;
                }
                array_push($filters[$filterKey], $value[$filterKey]);
            }
            $data[$key] = $priceRules->calculate($value);
        }
        return $filters;
    }
    private function filterData($data, $filters)
    {
        $data = collect($data);
        foreach ($filters as $filter) {
            if (!$filter['value']) {
                continue;
            }
            $data = $this->resolveQuery($filter['operator'], $filter['column'], $filter['value'], $data);
        }
        return  $data;
    }
    private function resolveQuery($operator,  $column, $value, $data)
    {
        switch ($operator) {
            case "in":
                return $data->whereIn($column, $value);
            case "between":
                return $data->whereBetween($column, $value);
            case "lowest-cost":
                $keys = [];
                $temp = collect($data->reduce(function ($carry, array $item) use (&$keys) {
                    $key = Str::slug(
                        $item['productName'] .
                            $item['providerName'] .
                            $item['connectivitySupplierName'] .
                            $item['bearer'] .
                            $item['speed'] .
                            $item['priceInstall'] .
                            $item['priceECC'] .
                            $item['contractTermMonths']
                    );
                    if (in_array($key, $keys)) {
                        if ($item['priceMonthly'] < $carry[$key]['priceMonthly']) {
                            $carry[$key] = $item;
                        }
                        return $carry;
                    }
                    array_push($keys, $key);
                    $carry[$key] = $item;
                    return $carry;
                }, []));
                return $temp->values();
            default:
                return $data;
        }
    }
}
