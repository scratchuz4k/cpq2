<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductAddonResource;
use App\Models\Account;
use App\Models\ProductAddon;
use App\Services\ProductAddonService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAddonController extends BaseController
{
    protected $model = ProductAddon::class;

    public function __construct(protected ProductAddonService $service)
    {
        parent::__construct();
    }

    /**
     * List of Product Addons
     */
    public function index()
    {
        $dataProvider = new DataProvider($this->model);

        try {
            $data = $dataProvider->getCollection();
        } catch (Exception $e) {
            return handleException($e);
        }

        $suppliers = [];
        $priceRules = new PriceRules;
        $account = Account::where('id', request()->input('account_id', null))->first();

        $data = $data->map(function ($row) use ($priceRules, $account, $suppliers) {

            if (array_key_exists($row->addon?->supplier?->id.$row->addon->pricePlan->code, $suppliers)) {
                $priceRules->setAppliedSpas($suppliers[$row->addon?->supplier?->id]);
            } else {
                $priceRules->find(supplier: $row->addon?->supplier, account: $account, pricePlan: $row->addon->pricePlan);
                $suppliers[$row->addon?->supplier?->id.$row->addon->pricePlan->code] = $priceRules->getAppliedSpas();
            }

            $row->addon->prices->map(function ($price) use ($priceRules) {
                $price['net_spa'] = (float) $priceRules->apply($price->net);
                $price['active_spas'] = $priceRules->getAppliedSpas();
            });

            return $row;
        });

        $data = ProductAddonResource::collection($data)->toArray(request());

        return FilterProvider::paginate(collect($data));

    }

    /**
     * Store a new Product Addon
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {

        try {
            $price = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductAddonResource($price->refresh());
    }

    /**
     * Show Product Addon
     */
    #[ExcludeRouteFromDocs]
    public function show(ProductAddon $productAddon)
    {
        //
    }

    /**
     * Update Product Addon
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, ProductAddon $productAddon)
    {

        try {
            $product = DB::transaction(fn () => $this->service->update($productAddon, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductAddonResource($product);
    }

    /**
     * Remove Product Addon
     */
    #[ExcludeRouteFromDocs]
    public function destroy(ProductAddon $productAddon)
    {
        $productAddon->delete();

        return response()->noContent();
    }
}
