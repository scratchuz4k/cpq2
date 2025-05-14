<?php

namespace App\Http\Controllers\Api\ShopBlocks;

use App\Helpers\Api\ShopBlocksApi;
use App\Helpers\FilterProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\External\ShopBlocksService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CategoryController extends BaseController
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
        $cache_key = md5('shop-blocks-categories');

        $res = Cache::remember($cache_key, 43200, function () {
            $products = [];
            $page = 0;
            do {
                $page++;
                $res = (new ShopBlocksApi)->getCategories(200, $page);
                if (! array_key_exists('data', $res)) {
                    break;
                }
                array_push($products, ...$res['data']);
                $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
            } while ($page != $last_page);

            $tempFilters = [];

            return ['items' => $products, 'filters' => $tempFilters];
        });

        $items = FilterProvider::get($res['items'], request()->input('filters', []));

        return new LengthAwarePaginator($items, $items->count(), 1000, 1);
    }

    /**
     * Show Product
     */
    // #[ExcludeRouteFromDocs]
    //     public function show(Product $product)
    //     {
    //         //
    //     }
}
