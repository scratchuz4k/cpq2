<?php

namespace App\Http\Controllers\Api\ShopBlocks;

use App\Enums\PricePlanEnum;
use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Models\Account;
use App\Models\PricePlan;
use App\Models\ShopBlocks\Product;
use App\Services\External\ShopBlocksService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends BaseController
{
    public function __construct(protected ShopBlocksService $shopBlocksService) {}

    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Product::class);
        try {
            $items = $dataProvider->getCollection();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $priceRules = new PriceRules;
        $account = Account::where('id', $request->input('account_id', null))->first();
        $pricePlan = PricePlan::where('code', PricePlanEnum::SINGLE->name)->first();
        $priceRules->find(account: $account, pricePlan: $pricePlan);

        $items = $items->map(function ($item) use ($priceRules) {
            $net_spa = $priceRules->apply($item->price['net']);
            $item['price'] = [
                'net' => $item->price['net'],
                'net_spa' => $net_spa,
                'tax' => $net_spa * 0.2,
                'gross' => $net_spa * 1.2,
                'active_spas' => $priceRules->getAppliedSpas(),
            ];

            return $item;
        });

        // $items = ProductResource::collection($items)->toArray(request());
        $paginator = FilterProvider::paginate(collect($items));

        return $paginator->toArray();

    }

    /**
     * Show Product
     */
    #[ExcludeRouteFromDocs]
    public function show(Product $product)
    {
        //
    }
}
