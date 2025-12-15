<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Account;
use App\Models\Product;
use App\Services\ProductsService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected $model = Product::class;

    public function __construct(protected ProductsService $service)
    {
        parent::__construct();
    }

    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Product::class);

        try {
            $data = $dataProvider->getCollection();
        } catch (Exception $e) {
            return handleException($e);
        }

        $suppliers = [];
        $priceRules = new PriceRules;
        $account = Account::where('id', request()->input('account_id', null))->first();

        $data = $data->map(function ($product) use ($priceRules, $account, $suppliers) {

            if (array_key_exists($product?->supplier?->id.$product->pricePlan->code, $suppliers)) {
                $priceRules->setAppliedSpas($suppliers[$product?->supplier?->id]);
            } else {
                $priceRules->find(supplier: $product?->supplier, account: $account, pricePlan: $product->pricePlan);
                $suppliers[$product?->supplier?->id.$product->pricePlan->code] = $priceRules->getAppliedSpas();
            }

            $product->prices->map(function ($price) use ($priceRules) {
                $price['net_spa'] = $priceRules->apply($price->net);
                $price['active_spas'] = $priceRules->getAppliedSpas();
            });

            return $product;
        });
        $data = ProductResource::collection($data->load(['prices', 'categories', 'steps']))->toArray(request());

        return FilterProvider::paginate(collect($data));
    }

    /**
     * Store a new Product
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Product
     */
    #[ExcludeRouteFromDocs]
    public function show(Product $product)
    {
        //
    }

    /**
     * Update Product
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Product $product)
    {
        try {
            $product = DB::transaction(fn () => $this->service->update($product, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductResource($product);
    }

    /**
     * Remove Product
     */
    public function destroy(Product $product)
    {
        $product->delete();
    }
}
