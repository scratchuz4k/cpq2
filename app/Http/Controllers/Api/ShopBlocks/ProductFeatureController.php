<?php

namespace App\Http\Controllers\Api\ShopBlocks;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ShopBlocks\ProductResource;
use App\Models\ShopBlocks\ProductFeature;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductFeatureController extends BaseController
{
    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(ProductFeature::class);
        try {
            $items = $dataProvider->get();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return JsonResource::collection($items);

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
