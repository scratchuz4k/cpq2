<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\BaseController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends BaseController
{
    protected $model = Product::class;
    /**
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    public function index()
    {
        $dataProvider = new DataProvider(Product::class);

        try {
            $data = $dataProvider->getCollection();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        $suppliers = [];
        $priceRules = new PriceRules();
        $account = Account::where('id', request()->input('account_id', null))->first();

        $data = $data->map(function ($product) use ($priceRules, $account, $suppliers) {
            if (array_key_exists($product?->supplier?->id, $suppliers)) {
                $priceRules->setAppliedSpas($suppliers[$product?->supplier?->id]);
            } else {
                $priceRules->find(supplier: $product?->supplier, account: $account);
                $suppliers[$product?->supplier?->id] = $priceRules->getAppliedSpas();
            }

            $product->prices->map(function ($price) use ($priceRules) {
                $price['net_spa'] = $priceRules->apply($price->net);
                $price['active_spas'] = $priceRules->getAppliedSpas();
            });

            return $product;
        });
        $data = ProductResource::collection($data)->toArray(request());
        return FilterProvider::paginate(collect($data));
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
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
    }
}
