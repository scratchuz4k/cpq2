<?php

namespace App\Http\Controllers\Api\ShopBlocks;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ShopBlocks\ProductDimensionResource;
use App\Http\Resources\ShopBlocks\ProductResource;
use App\Models\ShopBlocks\ProductDimension;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductDimensionController extends BaseController
{
    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(ProductDimension::class);
        try {
            $items = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return ProductDimensionResource::collection($items);

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
