<?php

namespace App\Http\Controllers\Api\ShopBlocks;

use App\Helpers\Api\ShopBlocksApi;
use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\ShopBlocks\Category;
use App\Models\ShopBlocks\Product;
use App\Services\External\ShopBlocksService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
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
        $dataProvider = new DataProvider(Category::class);
        try {
            $items = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return JsonResource::collection($items);

        // $cache_key = md5('shop-blocks-categories');

        // $res = Cache::remember($cache_key, 43200, function () {
        //     $products = [];
        //     $page = 0;
        //     do {
        //         $page++;
        //         $res = (new ShopBlocksApi)->getCategories(200, $page);
        //         if (! array_key_exists('data', $res)) {
        //             break;
        //         }
        //         array_push($products, ...$res['data']);
        //         $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
        //     } while ($page != $last_page);

        //     $tempFilters = [];

        //     return ['items' => $products, 'filters' => $tempFilters];
        // });

        // $items = FilterProvider::get($res['items'], request()->input('filters', []));

        // return new LengthAwarePaginator($items, $items->count(), 1000, 1);
    }

    public function tree(): JsonResponse
    {
        $all = Category::where('active', true)->get(['id', 'name', 'parent_id']);

        $map = [];
        foreach ($all as $c) {
            $map[$c->id] = array_merge($c->toArray(), ['children' => []]);
        }

        $roots = [];
        foreach ($map as $id => &$node) {
            if ($node['parent_id'] && isset($map[$node['parent_id']])) {
                $map[$node['parent_id']]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);

        return response()->json($roots);
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
