<?php

namespace App\Services\External;

use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\PricePlanEnum;
use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Files\BlacklistFile;
use App\Helpers\Files\CloudMarketFile;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Jobs\CloudMarketConnectivityProducts;
use App\Models\Account;
use App\Models\Api;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\SolutionType;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CloudMarketService
{
    protected $providers = [];

    public function get(Quote $quote, $connectivityType, CloudMarketAddress $address, $filters = [])
    {
        if (! env('CLOUDMARKET_CONNECTION_LIVE_DATA')) {
            $mock_prices = storage_path('mocks/prices.json');
            if (! is_file($mock_prices)) {
                return [];
            }

            return $this->sendResponse(json_decode(file_get_contents($mock_prices), true), $filters, $connectivityType);
        }

        $apis = array_map(fn ($case) => $case->value, CloudMarketSuppliersEnum::cases());
        $apiRecords = Api::active()->whereIn('code', $apis)->get();
        $apiCodes = $apiRecords->pluck('code')->sort()->implode('');

        $configs = '';
        $configs .= 'speed:'.(array_key_exists('speed', $quote->configs) ? $quote->configs['speed'] : '10');
        $configs .= 'bearer:'.(array_key_exists('bearer', $quote->configs) ? $quote->configs['bearer'] : '100');
        $configs .= 'contract_length:'.(array_key_exists('contract_length', $quote->configs) ? $quote->configs['contract_length'] : '0');
        $configs .= 'managed:'.(array_key_exists('managed', $quote->configs) ? $quote->configs['managed'] : '0');

        $filename = 'live_prices_'.$address->toString().'_'.md5($apiCodes.$configs).'.json';
        $path = storage_path('/app/products/');
        $file = new CloudMarketFile($path, $filename);
        $content = $file->getContent();
        $createdAt = Carbon::parse($content['created_at']);

        if (! count($apiRecords)) {
            $file->write([], true, now(), now(), [], [[
                'provider' => 'CPQ',
                'response' => 'No provider is configured',
            ]]);

            return $this->sendResponse($content, $filters, $connectivityType);
        }

        if (request()->input('clearCache', false)) {
            Cache::forget($filename);
        }

        if (! Cache::has($filename)) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($quote, $file, $connectivityType, $address);
        }

        if (! $file->exists() || (is_null($content['finished_at']) && $createdAt->addMinutes(10)->isPast())) {
            $file->write([], true, null, now());
            CloudMarketConnectivityProducts::dispatch($quote, $file, $connectivityType, $address);
        }

        return $this->sendResponse($file->getContent(), $filters, $connectivityType);
    }

    private function sendResponse(array $file, $filters, $connectivityType)
    {
        if (! is_array($file) || ! array_key_exists('data', $file)) {
            Log::error('Empty or invalid file content.', context: ['file' => $file]);
        }

        $items = FilterProvider::get($file['data'], []);

        $blacklist_file = new BlacklistFile(Auth::user()->currentTenant);
        $blacklist_items = $blacklist_file->getBlacklistedData($items);

        $diff_codes = $items->pluck('code')->diff($blacklist_items->pluck('code'));

        $items = $items->filter(function ($item) use ($diff_codes) {
            return $diff_codes->contains($item['code']);
        });

        $items = FilterProvider::get($items->toArray(), $filters);

        foreach ($items as $key => $value) {
            $file['filters'] = FilterProvider::process($file['filters'], $value, true);
        }

        $solutionType = SolutionType::where('code', $connectivityType)->first();
        $amortisation = array_key_exists('amortisation', $solutionType->configs) ? json_decode($solutionType->configs['amortisation'], true) : [];
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
                'overall_total' => $file['total'],
                'finished_at' => $file['finished_at'],
                'created_at' => $file['created_at'],
                'filters' => $file['filters'],
                'errors' => array_key_exists('errors', $file) ? $file['errors'] : [],
            ]
        );
    }
}
