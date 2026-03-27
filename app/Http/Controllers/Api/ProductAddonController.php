<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductAddonResource;
use App\Models\ProductAddon;
use App\Services\ProductAddonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAddonController extends BaseController
{
    protected $model = ProductAddon::class;

    public function __construct(protected ProductAddonService $service)
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

        return ProductAddonResource::collection($data);
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

        return new ProductAddonResource($price->refresh());
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductAddon $productAddon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductAddon $productAddon)
    {

        try {
            $product = DB::transaction(fn () => $this->service->update($productAddon, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductAddonResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductAddon $productAddon)
    {
        $productAddon->delete();

        return response()->noContent();
    }
}
