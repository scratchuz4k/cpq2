<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Services\BrandService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BrandResource;
use App\Http\Controllers\BaseController;

class BrandController extends BaseController
{
    protected $model = Brand::class;
    public function __construct(protected BrandService $brandService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Brands/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render(component: 'Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $brand = $this->brandService->store($request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('brands.edit', ['brand' => $brand->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        $temp = new BrandResource($brand);
        return Inertia::render('Brands/Edit', [
            "brand" => $temp->toArray(request())
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        try {
            $brand = $this->brandService->update($brand, $request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('brands.edit', ['brand' => $brand->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        //
    }
}
