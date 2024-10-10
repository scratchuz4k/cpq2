<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\SupplierCreateRequest;
use App\Http\Requests\SupplieUpdateRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SuppliersService;
use Inertia\Inertia;
use App\Http\Controllers\BaseController;

class SupplierController extends BaseController
{
    protected $model = Supplier::class;
    public function __construct(protected SuppliersService $suppliersService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Suppliers/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render(component: 'Suppliers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierCreateRequest $request)
    {
        try {
            $supplier = $this->suppliersService->store($request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('suppliers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $temp = new SupplierResource($supplier);
        return Inertia::render('Suppliers/Edit', [
            "supplier" => $temp->toArray(request())
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplieUpdateRequest $request, Supplier $supplier)
    {
        try {
            $supplier = $this->suppliersService->update($supplier, $request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('suppliers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        //
    }
}
