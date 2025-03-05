<?php

namespace App\Http\Controllers\Web;

use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\ComparisonOperatorsEnum;
use App\Enums\ContractTermsEnum;
use App\Enums\SpeedUnitsEnum;
use Inertia\Inertia;
use App\Models\Brand;
use Inertia\Response;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\PricePlan;

class BlacklistController extends BaseController
{
    public function __construct(protected ProductsService $productsService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Blacklist/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Blacklist/Create', [
            'providers' => CloudMarketSuppliersEnum::asArray(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
//        try {
//            $product = DB::transaction(function () use ($request) {
//                return $this->productsService->store($request->all());
//            });
//        } catch (\Exception $e) {
//            if (!env('APP_DEBUG')) {
//                Log::error($e->getMessage());
//            }
//            throw new \Exception($e);
//        }
//        return redirect()->route('products.index');
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

        return Inertia::render('Blacklist/Edit', [
            'product' => ProductResource::make($product->load(['prices','categories']))->resolve(),
            'price_plans' => PricePlan::all(),
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
