<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;

class ProductController extends BaseController
{
    protected $model = Product::class;

    public function __construct(protected ProductsService $productsService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Products/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'brands' => Brand::all(),
            'suppliers' => Supplier::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        try {
            $product = DB::transaction(function () use ($request) {
                return $this->productsService->store($request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return Inertia::render('Products/Show', [
            'product' => new ProductResource($product->load('prices'))
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {

        return Inertia::render('Products/Edit', [
            'product' => (new ProductResource($product->load(['prices'])))->resolve(),
            'brands' => Brand::all(),
            'suppliers' => Supplier::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        try {
            $product = DB::transaction(function () use ($product, $request) {
                return $this->productsService->update($product, $request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
