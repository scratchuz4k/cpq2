<?php

namespace App\Http\Controllers\Api\Cloudmarket;

use App\Enums\CloudMarketSuppliersEnum;
use App\Helpers\Api\CloudMarketAddress;
use App\Http\Controllers\BaseController;
use App\Jobs\CloudMarketAddonsProducts;
use App\Jobs\CloudMarketAssignmentsProducts;
use App\Jobs\CloudMarketConnectivityProducts;
use App\Jobs\CloudMarketFailOverProducts;
use App\Jobs\CloudMarketOptionsProducts;
use App\Jobs\CloudMarketSaveQuote;
use App\Models\Api;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use App\Services\External\CloudMarketService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseController
{
    public function __construct(protected CloudMarketService $cloudMarketService) {}

    #[ExcludeRouteFromDocs]
    public function index(Request $request)
    {
        // Special-cased mock data for index only
        $filters = $this->decodeFilters($request);
        if (! config('services.cloudmarket.connection_live_data')) {
            $mock_prices = storage_path('mocks/prices.json');
            if (! is_file($mock_prices)) {
                return [];
            }
            $items = json_decode(file_get_contents($mock_prices), true);

            return $this->cloudMarketService->sendResponse($items, $filters, 3);
        }

        return $this->handleProductRequest($request, 'live_prices', CloudMarketConnectivityProducts::class, false, false);
    }

    #[ExcludeRouteFromDocs]
    public function failover(Request $request)
    {
        return $this->handleProductRequest($request, 'fail_over', CloudMarketFailOverProducts::class, true);
    }

    #[ExcludeRouteFromDocs]
    public function addons(Request $request)
    {
        return $this->handleProductRequest($request, 'addons', CloudMarketAddonsProducts::class, true, true);
    }

    #[ExcludeRouteFromDocs]
    public function options(Request $request)
    {
        return $this->handleProductRequest($request, 'options', CloudMarketOptionsProducts::class, true, true);
    }

    #[ExcludeRouteFromDocs]
    public function assignments(Request $request)
    {
        return $this->handleProductRequest($request, 'assignments', CloudMarketAssignmentsProducts::class, true, true);
    }

    #[ExcludeRouteFromDocs]
    public function saveQuote(Request $request)
    {
        if (! config('services.cloudmarket.connection_live_data')) {
            return;
        }

        $quote = $this->getQuote($request);
        $address = $this->generateAddress($quote);
        $filename = $this->generateFilename($quote, $address);
        $data = $quote->data ?? [];
        $data['filename'] = $filename;
        $quote->update(['data' => $data]);
        $product = $this->getProduct($quote, 'Primary Product');

        if (! $product || ($product->data?->providerName !== 'ITS' && $product->data?->providerName !== 'PXC')) {
            return;
        }

        try {
            $job = new CloudMarketSaveQuote($quote);
            $results = $job->runSync();
        } catch (Exception $e) {
            return handleException($e, 'Something went wrong while saving your quote.');
        }
    }

    private function handleProductRequest(
        Request $request,
        string $key,
        string $jobClass,
        bool $requirePrimary = true,
        bool $appendFailover = false
    ) {/**/
        $filters = $this->decodeFilters($request);
        $quote = $this->getQuote($request);

        $address = $this->generateAddress($quote);
        $filename = $this->generateFilename($quote, $address);

        if ($request->has('clear_cache') && (bool) $request->get('clear_cache')) {
            Cache::forget($filename);
            Product::where('filename', $filename)->where('key', $key)->delete();
        }

        if ($requirePrimary) {
            $product = $this->getProduct($quote, 'Primary Product');
            if (! $product) {
                return response()->json(['message' => 'Primary Product not found!'], 400);
            }
            $filename = $product->product_code.$filename;

            if ($appendFailover) {
                $failover = $this->getProduct($quote, 'Failover Product');
                $filename = $product->product_code.($failover?->data?->productCode ?? 'failover').$filename;
            }
        }

        $this->cloudMarketService->handleProductJob($key, $filename, $quote, $address, $jobClass);
        $content = Product::where('key', $key)->where('filename', $filename)->first();

        return $this->cloudMarketService->sendResponse($content?->toArray() ?? [], $filters, 3);
    }

    private function decodeFilters(Request $request)
    {
        if ($request->has('filters') && is_string($request->filters)) {
            return json_decode($request->filters, true);
        }
        if ($request->has('filters') && is_array($request->filters)) {
            return $request->filters;
        }

        return [];
    }

    private function getQuote(Request $request)
    {
        $quote = Quote::find($request->get('quote_id'));
        if (! $quote) {
            throw new Exception('Quote not found!');
        }

        return $quote;
    }

    private function getProduct($quote, $type)
    {
        return $quote->items->first(function ($x) use ($type) {
            return in_array(data_get($x, 'data.type'), [$type], true);
        });
    }

    private function generateAddress(Quote $quote)
    {
        $address = (object) $quote->address_data;

        return new CloudMarketAddress(
            postCode: $address->postCode ?? null,
            uprn: $address->uprn ?? null,
            districtCode: $address->districtCode ?? null,
            lat: $address->latitude ?? null,
            lon: $address->longitude ?? null,
            addressKey: $address->addressKey ?? null,
            buildingName: $address->premisesName ?? $address->organisationName ?? null,
            buildingNumber: $address->buildingNumber ?? $address->thoroughfareNumber ?? null,
            subPremises: $address->subPremises ?? null,
            street: $address->thoroughfareName ?? $address->dependentThoroughfareName ?? null,
            town: $address->postTown ?? $address->locality ?? $address->doubleDependentLocality ?? null,
        );

    }

    private function generateFilename(Quote $quote, CloudMarketAddress $address)
    {

        $apis = array_map(fn ($case) => $case->value, CloudMarketSuppliersEnum::cases());
        $apiRecords = Api::active()->whereIn('code', $apis)->get();
        $apiCodes = $apiRecords->pluck('code')->sort()->implode('');

        $configs = '';
        $configs .= 'speed:'.(array_key_exists('speed', $quote->configs) ? $quote->configs['speed'] : '10');
        $configs .= 'bearer:'.(array_key_exists('bearer', $quote->configs) ? $quote->configs['bearer'] : '100');
        $configs .= 'contract_length:'.(array_key_exists('contract_length', $quote->configs) ? $quote->configs['contract_length'] : '0');

        $filename = $address->toString().'_'.md5($apiCodes.$configs);
        if (! count($apiRecords)) {
            Product::write('live_prices', $filename, [], true, now(), now(), [], [[
                'provider' => 'CPQ',
                'response' => 'No provider is configured',
            ]]);
        }

        if (is_array($quote->data) && isset($quote->data['quoteReference'])) {
            $filename = $filename.$quote->data['quoteReference'];
        }

        return $filename;
    }
}
