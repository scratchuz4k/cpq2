<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SuppliersService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends BaseController
{
    protected $model = Supplier::class;

    public function __construct(protected SuppliersService $service)
    {
        parent::__construct();
    }

    /**
     * List of Suppliers
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<SupplierResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Supplier::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return SupplierResource::collection($data);
    }

    /**
     * Store a new Supplier
     */
    public function store(Request $request)
    {
        try {
            $supplier = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new SupplierResource($supplier);
    }

    /**
     * Show Supplier
     */
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    /**
     * Update Supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        try {
            $supplier = DB::transaction(fn () => $this->service->update($supplier, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new SupplierResource($supplier);
    }

    /**
     * Remove Supplier
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
    }
}
