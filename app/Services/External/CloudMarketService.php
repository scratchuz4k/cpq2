<?php

namespace App\Services\External;

use App\Enums\PricePlanEnum;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Jobs\CloudMarketConnectivityProducts;
use App\Models\Account;
use App\Models\Cloudmarket\Product;
use App\Models\General\Blacklist;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\SolutionType;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CloudMarketService
{
    protected $providers = [];

    public function get(string $filename, Quote $quote, $address)
    {

        $content = Product::where('key', $filename)->first();
        $createdAt = Carbon::parse($content?->created_at);

        if (request()->input('clearCache', false)) {
            Cache::forget($filename);
        }

        if (! Cache::has($filename)) {
            Product::write($filename, [], true, null, now());
            CloudMarketConnectivityProducts::dispatch($quote, $filename, $address);
        }

        if (is_null($content?->finished_at) && $createdAt->addMinutes(10)->isPast()) {
            Product::write($filename, [], true, null, now());
            CloudMarketConnectivityProducts::dispatch($quote, $filename, $address);
        }

    }

    public function sendResponse(string $filename, $filters, $connectivityType)
    {
        $content = Product::where('key', $filename)->first();
        $items = FilterProvider::get($content['data'], []);

        $blacklist = new Blacklist;
        $blacklist_items = $blacklist->getBlacklistedData($items);

        $diff_codes = $items->pluck('code')->diff($blacklist_items->pluck('code'));

        $items = $items->filter(fn ($item) => $diff_codes->contains($item['code']));

        $items = FilterProvider::get($items->toArray(), $filters);

        foreach ($items as $key => $value) {
            $content['filters'] = FilterProvider::process($content['filters'], $value, true);
        }

        $solutionType = SolutionType::where('code', $connectivityType)->first();
        $amortisation = array_key_exists('amortisation', $solutionType->configs) ? json_decode((string) $solutionType->configs['amortisation'], true) : [];
        $priceRules = new PriceRules;

        $account = Account::where('id', request()->input('account_id', null))->first();
        $priceplans = [
            'priceMonthly' => PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first(),
            'priceAnnual' => PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first(),
            'priceInstall' => PricePlan::where('code', PricePlanEnum::SINGLE->name)->first(),
        ];

        $items = $items->map(function ($product) use ($priceRules, $account, $priceplans, $amortisation) {
            $supplier = null;

            foreach ($priceplans as $key => $priceplan) {
                if (array_key_exists($product['providerKey'].$priceplan->code, $this->providers)) {
                    $priceRules->setAppliedSpas($this->providers[$product['providerKey'].$priceplan->code]);
                } else {
                    $supplier = Supplier::where('reference', $product['providerKey'])->first();
                    $priceRules->find(supplier: $supplier, account: $account, pricePlan: $priceplan);
                    $this->providers[$product['providerKey'].$priceplan->code] = $priceRules->getAppliedSpas();
                }

                if (! array_key_exists($key, $product)) {
                    continue;
                }
                $product[$key.'_spa'] = $priceRules->apply($product[$key] ?? 0);
            }

            $product['active_spas'] = $priceRules->getAppliedSpas();
            if (array_key_exists($product['providerKey'], $amortisation) && in_array($product['contractTermMonths'], $amortisation[$product['providerKey']])) {
                $product['amortisation_install'] = $product['priceInstall_spa'];
                $product['amortisation'] = $product['priceInstall_spa'] / $product['contractTermMonths'];
                $product['priceMonthly_spa'] += $product['amortisation'];
                $product['priceInstall_spa'] = 0;
            }

            return $product;
        });

        $paginator = FilterProvider::paginate($items);
        $response = $paginator->toArray();

        return array_merge(
            $response,
            [
                'overall_total' => $content['total'],
                'finished_at' => $content['finished_at'],
                'created_at' => $content['created_at'],
                'filters' => $content['filters'],
                'errors' => array_key_exists('errors', $content) ? $content['errors'] : [],
            ]
        );
    }
}
