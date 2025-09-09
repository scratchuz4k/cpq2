<?php

namespace App\Http\Controllers\Api\Cloudmarket;

use App\Enums\CloudMarketSuppliersEnum;
use App\Helpers\Api\CloudMarketAddress;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ShopBlocks\ProductResource;
use App\Models\Api;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use App\Services\External\CloudMarketService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends BaseController
{
    public function __construct(protected CloudMarketService $cloudMarketService) {}

    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(Request $request)
    {
        $filters = $request->has('filters') ? json_decode($request->filters, true) : [];
        if (! env('CLOUDMARKET_CONNECTION_LIVE_DATA')) {
            $mock_prices = storage_path('mocks/prices.json');
            if (! is_file($mock_prices)) {
                return [];
            }
            $items = json_decode(file_get_contents($mock_prices), true);

            return $this->cloudMarketService->sendResponse($items, $filters, 3);
        }

        $quote = Quote::find($request->get('quote_id'));
        if (! $quote) {
            throw new Exception('Quote not found!');
        }
        $address = (object) $quote->address_data;

        $address = new CloudMarketAddress(
            postCode: $address->postCode ?? null,
            uprn: $address->uprn ?? null,
            lat: $address->latitude ?? null,
            lon: $address->longitude ?? null,
            addressKey: $address->addressKey ?? null,
            buildingName: $address->premisesName ?? $address->organisationName ?? null,
            buildingNumber: $address->buildingNumber ?? null,
            subPremises: $address->subPremises ?? null,
            street: $address->thoroughfareName ?? $address->dependentThoroughfareName ?? null,
            town: $address->postTown ?? $address->locality ?? $address->doubleDependentLocality ?? null,
        );

        $apis = array_map(fn ($case) => $case->value, CloudMarketSuppliersEnum::cases());
        $apiRecords = Api::active()->whereIn('code', $apis)->get();
        $apiCodes = $apiRecords->pluck('code')->sort()->implode('');

        $configs = '';
        $configs .= 'speed:'.(array_key_exists('speed', $quote->configs) ? $quote->configs['speed'] : '10');
        $configs .= 'bearer:'.(array_key_exists('bearer', $quote->configs) ? $quote->configs['bearer'] : '100');
        $configs .= 'contract_length:'.(array_key_exists('contract_length', $quote->configs) ? $quote->configs['contract_length'] : '0');
        $configs .= 'managed:'.(array_key_exists('managed', $quote->configs) ? $quote->configs['managed'] : '0');

        $filename = $address->toString().'_'.md5($apiCodes.$configs).'.json';
        if (! count($apiRecords)) {
            Product::write('live_prices', $filename, [], true, now(), now(), [], [[
                'provider' => 'CPQ',
                'response' => 'No provider is configured',
            ]]);
        }

        // $this->cloudMarketService->getConnectivityProducts($filename, $quote, $address);
        $this->cloudMarketService->getFailoverProducts($filename, $quote, $address);
        $content = Product::where('key', $request->input('key', 'live_prices'))->where('filename', $filename)->first();

        return $this->cloudMarketService->sendResponse($content->toArray(), $filters, 3);
    }
}
