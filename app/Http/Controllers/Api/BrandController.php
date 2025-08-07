<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrandController extends BaseController
{
    protected $model = Brand::class;

    public function __construct(protected BrandService $brandService)
    {
        parent::__construct();
    }

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
        try {
            $brand = DB::transaction(function () use ($brand, $request) {
                return $this->brandService->update($brand, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new BrandResource($brand);
    }

    /**
     * Remove Brand
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
    }
}
