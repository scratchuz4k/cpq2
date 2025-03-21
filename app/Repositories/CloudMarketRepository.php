<?php

namespace App\Repositories;

use App\Enums\PricePlanEnum;
use App\Models\Supplier;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Models\Account;
use App\Models\PricePlan;
use Illuminate\Support\Facades\Log;

class CloudMarketRepository
{

    protected $providers = [];

    public function sendResponse(array $file, $filters)
    {
        if (!is_array($file) || !array_key_exists('data', $file)) {
            Log::error('Empty or invalid file content.', ['file' => $file]);
        }

        $items = FilterProvider::get($file['data'], $filters);

        foreach ($items as $key => $value) {
            $file['filters'] = FilterProvider::process($file['filters'], $value, true);
        }


        $priceRules = new PriceRules();
        $account = Account::where('id', request()->input('account_id', null))->first();
        $priceplans  = [
            'priceMonthly' => PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first(),
            'priceAnnual' => PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first(),
            'priceInstall' => PricePlan::where('code', PricePlanEnum::SINGLE->name)->first()
        ];

        $items = $items->map(function ($product) use ($priceRules, $account, $priceplans) {
            $supplier = null;

            foreach ($priceplans as $key => $priceplan) {
                if (array_key_exists($product['providerKey'] . $priceplan->code, $this->providers)) {
                    $priceRules->setAppliedSpas($this->providers[$product['providerKey'] . $priceplan->code]);
                } else {
                    $supplier = Supplier::where('reference', $product['providerKey'])->first();
                    $priceRules->find(supplier: $supplier, account: $account, pricePlan: $priceplan);
                    $this->providers[$product['providerKey'] . $priceplan->code] = $priceRules->getAppliedSpas();
                }

                if (!array_key_exists($key, $product)) {
                    continue;
                }
                $product[$key . '_spa'] = $priceRules->apply($product[$key] ?? 0);
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
}
