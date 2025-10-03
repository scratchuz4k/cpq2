<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SuppliersService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            $supplier = DB::transaction(fn () => $this->service->update($supplier, $request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
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
