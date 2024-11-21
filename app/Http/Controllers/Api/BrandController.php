<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
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
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<BrandResource>>
     */
    public function index()
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
    }
}
