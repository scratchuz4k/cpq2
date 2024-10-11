<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Helpers\Prices\PriceRulesConnectivity;

class CloudMarketRepository
{

    public function sendResponse($file, $filters)
    {
        $filtersData = $this->processPricesAndFilters($file['data']);
        $items = $this->filterData($file['data'], $filters);

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
            'overall_total' => $file['total'],
            'finished_at' => $file['finished_at'],
            'created_at' =>  $file['created_at'],
            'filters' =>  $filtersData,
        ];
    }
    public function processPricesAndFilters(&$data)
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
    public function filterData($data, $filters)
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
