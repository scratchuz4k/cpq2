<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Services\PriceService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceController extends BaseController
{
    protected $model = Price::class;

    public function __construct(protected PriceService $priceService)
    {
        parent::__construct();
    }

    /**
     * List of Prices
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<PriceResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Price::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return PriceResource::collection($data);
    }

    /**
     * Store a new Price
     */
    public function store(PriceCreateRequest $request)
    {
        try {
            $price = DB::transaction(fn () => $this->priceService->store($request->all()));
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }

        return new PriceResource($price->refresh());
    }

    /**
     * Show Price
     */
    public function show(Price $price)
    {
        return new PriceResource($price);
    }

    /**
     * Update Price
     */
    public function update(PriceUpdateRequest $request, Price $price)
    {
        try {
            $price = DB::transaction(fn () => $this->priceService->update($price, $request->all()));
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }

        return new PriceResource($price->refresh());
    }

    /**
     * Remove Price
     */
    public function destroy(Price $price)
    {
        $price->delete();
    }
}
