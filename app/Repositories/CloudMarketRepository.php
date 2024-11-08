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
        foreach ($file['data'] as $index => $product) {
            $supplier = Supplier::where('reference', $product['providerKey'])->first();
            $priceRules->find(supplier: $supplier);
            foreach (['priceMonthly', 'priceAnnual', 'priceInstall'] as $key) {
                if (!array_key_exists($key, $product)) {
                    continue;
                }

                $file['data'][$index][$key] =  $priceRules->apply($product[$key]);
            }

            $file['data'][$index]['active_spas']  = $priceRules->getAppliedSpas();
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
