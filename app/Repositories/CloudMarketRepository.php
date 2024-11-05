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
            $file['filters'] = FilterProvider::process($file['filters'], $value, true);
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
}
