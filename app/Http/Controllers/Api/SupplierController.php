<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierController extends BaseController
{
    protected $model = Supplier::class;

    /**
     * List of Suppliers
     * @response AnonymousResourceCollection<LengthAwarePaginator<SupplierResource>>
     */
    public function index(IndexRequest $request)
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
     * Store a new Supplier
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Supplier
     */
    #[ExcludeRouteFromDocs]
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Update Supplier
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    /**
     * Remove Supplier
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
    }
}
