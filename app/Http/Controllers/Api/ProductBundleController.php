<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductBundleResource;
use App\Models\ProductBundle;
use App\Services\ProductBundleService;
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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
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
     * Display the specified resource.
     */
    public function show(ProductBundle $productBundle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
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
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBundle $productBundle)
    {
        //
    }
}
