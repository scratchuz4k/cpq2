<?php

namespace App\Repositories;

use App\Helpers\FilterProvider;
use Illuminate\Support\Str;
use App\Helpers\Prices\PriceRulesConnectivity;

class CloudMarketRepository
{

    public function sendResponse($file, $filters)
    {
        $priceRules = new PriceRulesConnectivity();
        foreach ($file['data'] as $key => $value) {
            $file['data'][$key] = $priceRules->calculate($value);
        }

        $items = FilterProvider::get($file['data'], $filters);

        foreach ($items as $key => $value) {
            $file['filters'] = $this->processFilters($file['filters'], $value, true);
        }

        $meta = FilterProvider::paginate($items);
        return array_merge(
            $meta,
            [
                'overall_total' => $file['total'],
                'finished_at' => $file['finished_at'],
                'created_at' =>  $file['created_at'],
                'filters' =>  $file['filters'],
            ]
        );
    }


    public function processFilters($filters, $value, $addCount = false)
    {
        foreach ($filters as $filterKey => $items) {
            if (!array_key_exists($filterKey, $value) || !$value[$filterKey]) {
                continue;
            }

            if (array_key_exists($value[$filterKey], $items) && $addCount) {

                $filters[$filterKey][$value[$filterKey]]++;
                continue;
            } else {
                $filters[$filterKey][$value[$filterKey]] = 0;
            }
        }

        return $filters;
    }
}
