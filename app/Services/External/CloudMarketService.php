<?php

namespace App\Services\External;

use App\Enums\PricePlanEnum;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
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

    public function handleProductJob($key, $filename, Quote $quote, $address, $dispatchJob)
    {
        $time = Carbon::now()->addSeconds(86400);
        if (! Cache::has($filename)) {
            Product::write($key, $filename, [], true, null, now(), [], [], $time);
            $dispatchJob::dispatch($quote, $filename, $address);
        }

        $content = Product::where('key', $key)->where('filename', $filename)->first();
        $createdAt = Carbon::parse($content?->created_at);

        if (! $content || (is_null($content?->finished_at) && $createdAt->addMinutes(20)->isPast())) {
            Product::write($key, $filename, [], true, null, now(), [], [], $time);
            $dispatchJob::dispatch($quote, $filename, $address);
        }
    }

    public function sendResponse(array $content, $filters, $connectivityType)
    {
        $items = FilterProvider::get($content['data'] ?? [], []);

        $blacklist = new Blacklist;
        $blacklist_items = $blacklist->getBlacklistedData($items);

        $diff_codes = $items->pluck('code')->diff($blacklist_items->pluck('code'));

        $items = $items->filter(fn ($item) => $diff_codes->contains($item['code']));

        $items = FilterProvider::get($items->toArray(), $filters);

        foreach ($items as $key => $value) {
            if (! isset($content['filters']) || is_null($content['filters'])) {
                continue;
            }
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
            if (array_key_exists($product['providerKey'], $amortisation) && in_array($product['contractTermMonths'] ?? null, $amortisation[$product['providerKey']])) {
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
                'options' => array_key_exists('options', $content) ? $content['options'] : [],
                'overall_total' => $content['total'],
                'finished_at' => $content['finished_at'],
                'created_at' => $content['created_at'],
                'filters' => $content['filters'],
                'errors' => isset($content['errors']) ? $this->mapProviderErrors($content['errors']) : [],
            ]
        );
    }

    protected function mapProviderErrors(array $errors): array
    {
        $errorMessages = [
            'BadGateway' => 'A Bad Gateway error occurred, resulting in partial or no results.',
            'Request timed out' => 'The request timed out, resulting in partial or no results.',
            'InternalServerError' => 'An error occurred on the server.',
            'An error occurred while sending the request' => 'There was an issue sending your request.',
            'TooManyRequests' => 'The system is experiencing a high volume of requests.',
            'Too Many Attempts' => 'There are currently too many requests being processed.',
            'System.Text.Json.JsonException' => 'The request could not be processed due to a data format issue.',
            'Service Unavailable' => 'The service is temporarily unavailable.',
            'There is an error in XML document' => 'The request could not be processed due to a data format issue.',
            'No provider is configured' => 'No provider is configured, please add at least one provider with the required credentials to obtain a quote.',
            'Request failed with status Unauthorized' => 'Request failed with status Unauthorized (Please check login credentials for this provider).',
            'Address not found in cache' => 'No results were found for this address.',
            'At least one of Building Name or Number is required' => 'At least one of Building Name or Number is required.',
            'Products are not available at' => 'No products were found for this location.',
            'No results for this location' => 'No results were found for this location.',
            'VMB requires one of UPRN or Lat/Long for the request' => 'VMO2 requires one of UPRN or Latitude/Longitude for the request.',
            'Latitude not set' => 'There is no latitude set for this address.',
            'Longitude not set' => 'There is no longitude set for this address.',
            'Street is not set' => 'There is no street set for this address.',
        ];

        $mappedErrors = [];

        foreach ($errors as $error) {
            if (($error['provider'] ?? null) === 'gamma') {
                continue;
            }

            $provider = $error['provider'] ?? null;
            $responseText = (string) ($error['response'] ?? '');

            $mappedMessage = null;
            foreach ($errorMessages as $key => $message) {
                if (str_contains($responseText, $key)) {
                    $mappedMessage = $message;
                    break;
                }
            }

            $mappedErrors[] = [
                'provider' => $provider,
                'response' => $mappedMessage ?? 'An error occurred, resulting in partial or no results',
            ];
        }

        $unique = [];
        foreach ($mappedErrors as $e) {
            $uniqKey = ($e['provider'] ?? '').'|'.($e['response'] ?? '');
            $unique[$uniqKey] = $e;
        }

        return array_values($unique);
    }
}
