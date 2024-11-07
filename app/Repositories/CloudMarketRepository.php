<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;

class CloudMarketRepository
{

    public function sendResponse($file, $filters)
    {
        $priceRules = new PriceRules();
        foreach ($file['data'] as $key => $product) {
            $supplier = Supplier::where('reference', $product['providerKey'])->first();
            foreach (['priceMonthly', 'priceAnnual', 'priceInstall'] as $key) {
                if (!array_key_exists($key, $product)) {
                    continue;
                }

                $product[$key] =  $priceRules->calculate($product[$key], supplier: $supplier);
            }

            $product['used_spas']  = $priceRules->getAppliedSpas();
            $file['data'][$key] = $product;
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
