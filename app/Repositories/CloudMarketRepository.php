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
        $items = FilterProvider::get($file['data'], $filters);

        foreach ($items as $key => $value) {
            $file['filters'] = FilterProvider::process($file['filters'], $value, true);
        }


        $priceRules = new PriceRules();
        $account = Account::where('id', request()->input('account_id', null))->first();
        $items = $items->map(function ($product) use ($priceRules, $account) {
            $supplier = null;

            if (array_key_exists($product['providerKey'], $this->providers)) {
                $priceRules->setAppliedSpas($this->providers[$product['providerKey']]);
            } else {
                $supplier = Supplier::where('reference', $product['providerKey'])->first();
                $priceRules->find(supplier: $supplier, account: $account);
                $this->providers[$product['providerKey']] = $priceRules->getAppliedSpas();
            }

            foreach (['priceMonthly', 'priceAnnual', 'priceInstall'] as $key) {
                if (!array_key_exists($key, $product)) {
                    continue;
                }
                $product[$key . '_spa'] = $priceRules->apply($product[$key]);
            }

            $product['active_spas'] = $priceRules->getAppliedSpas();

            return $product;
        });

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

    function getConsolidatedProviderGroup($provider): string
    {
        $sub_provider_mapping = [
            'BT' => ['BT Wholesale', 'BTW', 'BT'],
            'Virgin' => ['Virgin Media', 'Virgin Media Business', 'Virgin'],
            'ITS' => ['ITS Technology Group', 'ITS'],
            'TalkTalk' => ['TalkTalk Business', 'TalkTalk']
        ];

        foreach ($sub_provider_mapping as $consolidated_name => $sub_providers) {
            if (in_array($provider, $sub_providers)) {
                return $consolidated_name;
            }
        }

        return $provider;
    }
}
