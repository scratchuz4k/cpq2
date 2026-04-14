<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Helpers\FilterProvider;
use App\Helpers\Prices\PriceRules;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductLinkCreateRequest;
use App\Http\Requests\ProductLinkUpdateRequest;
use App\Http\Resources\ProductLinkResource;
use App\Models\Account;
use App\Models\ProductLink;
use App\Services\ProductLinkService;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductLinkController extends BaseController
{
    protected $model = ProductLink::class;

    public function __construct(protected ProductLinkService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
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

            if (array_key_exists($row->child?->supplier?->id.$row->child->pricePlan->code, $suppliers)) {
                $priceRules->setAppliedSpas($suppliers[$row->child?->supplier?->id]);
            } else {
                $priceRules->find(supplier: $row->child?->supplier, account: $account, pricePlan: $row->child->pricePlan);
                $suppliers[$row->child?->supplier?->id.$row->child->pricePlan->code] = $priceRules->getAppliedSpas();
            }

            $row->child->prices->map(function ($price) use ($priceRules) {
                $price['net_spa'] = (float) $priceRules->apply($price->net);
                $price['active_spas'] = $priceRules->getAppliedSpas();
            });

            return $row;
        });

        $data = ProductLinkResource::collection($data)->toArray(request());

        return FilterProvider::paginate(collect($data));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductLinkCreateRequest $request)
    {
        try {
            $price = DB::transaction(fn () => $this->service->store($request->validated()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductLinkResource($price->refresh());
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductLink $productLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductLinkUpdateRequest $request, ProductLink $productLink)
    {
        try {
            $product = DB::transaction(fn () => $this->service->update($productLink, $request->validated()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new ProductLinkResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductLink $productLink)
    {
        $productLink->delete();

        return response()->json(null, 204);
    }
}
