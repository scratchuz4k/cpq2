<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Exception;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;

class SupplierController extends BaseController
{
    protected $model = Supplier::class;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(Supplier::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return SupplierResource::collection($data);
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
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
    }
}
