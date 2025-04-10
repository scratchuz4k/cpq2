<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandController extends BaseController
{
    protected $model = Brand::class;
    /**
     * List Brands
     * @response AnonymousResourceCollection<LengthAwarePaginator<BrandResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Brand::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return BrandResource::collection($data);
    }

    /**
     * Create a new Brand
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Brand
     *
     */
    #[ExcludeRouteFromDocs]
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update Brand
     *
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Brand $brand)
    {
        //
    }

    /**
     * Remove Brand
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
    }
}
