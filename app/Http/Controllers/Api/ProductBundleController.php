<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductBundleResource;
use App\Models\ProductBundle;
use App\Services\ProductBundleService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProductBundleController extends BaseController
{
    protected $model = ProductBundle::class;

    public function __construct(protected ProductBundleService $service)
    {
        parent::__construct();
    }

    /**
     * List of Product Bundles
     */
    public function index()
    {
        $dataProvider = new DataProvider($this->model);

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return ProductBundleResource::collection($data);
    }

    /**
     * Store a new Product Bundle
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {

        try {
            $price = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new JsonResource($price->refresh());
    }

    /**
     * Show Product Bundle
     */
    #[ExcludeRouteFromDocs]
    public function show(ProductBundle $productBundle)
    {
        //
    }

    /**
     * Update Product Bundle
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, ProductBundle $productBundle)
    {

        try {
            $product = DB::transaction(fn () => $this->service->update($productBundle, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new JsonResource($product);
    }

    /**
     * Remove Product Bundle
     */
    #[ExcludeRouteFromDocs]
    public function destroy(ProductBundle $productBundle)
    {
        $productBundle->delete();

        return response()->noContent();
    }
}
