<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Resource;
use App\Services\ProductsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

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
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Products/Index', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'price_plans' => PricePlan::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        try {
            $product = DB::transaction(fn () => $this->productsService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {

        return Inertia::render('Products/Edit', [
            'product' => ProductResource::make($product->load(['prices', 'categories', 'steps']))->resolve(),
            'price_plans' => PricePlan::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        try {
            $product = DB::transaction(fn () => $this->productsService->update($product, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
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
