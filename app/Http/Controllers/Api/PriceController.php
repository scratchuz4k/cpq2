<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Price;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\PriceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PriceResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PriceController extends BaseController
{
    protected $model = Price::class;
    public function __construct(protected PriceService $priceService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<PriceResource>>
     */
    public function index()
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
     * Store a newly created resource in storage.
     */
    public function store(PriceCreateRequest $request)
    {
        try {
            $price = DB::transaction(function () use ($request) {
                return $this->priceService->store($request->all());
            });
        } catch (Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }
        return new PriceResource($price);
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price)
    {
        return new PriceResource($price);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceUpdateRequest $request, Price $price)
    {
        try {
            $price = DB::transaction(function () use ($price, $request) {
                return $this->priceService->update($price, $request->all());
            });
        } catch (Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }
        return new PriceResource($price->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        $price->delete();
    }
}
