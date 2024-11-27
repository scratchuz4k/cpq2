<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Models\Account;

class CloudMarketRepository
{

    protected $providers = [];

    public function sendResponse($file, $filters)
    {
        $priceRules = new PriceRules();
        $account = Account::where('id', request()->input('account_id', null))->first();
        foreach ($file['data'] as $index => $product) {
            $supplier = null;

            if (array_key_exists($product['providerKey'], $this->providers)) {
                $supplier = $this->providers[$product['providerKey']];
            } else {
                $supplier = Supplier::where('reference', $product['providerKey'])->first();
                $this->providers[$product['providerKey']] = $supplier;
            }

            $priceRules->find(supplier: $supplier, account: $account);
            foreach (['priceMonthly', 'priceAnnual', 'priceInstall'] as $key) {
                if (!array_key_exists($key, $product)) {
                    continue;
                }

                $file['data'][$index][$key . '_spa'] =  $priceRules->apply($product[$key]);
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
                'errors' =>  array_key_exists('errors', $file) ? $file['errors'] : [],
            ]
        );
    }
}
