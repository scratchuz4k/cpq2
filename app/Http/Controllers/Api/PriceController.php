<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Price;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PriceResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;

class PriceController extends BaseController
{
    protected $model = Price::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $dataProvider = new DataProvider(Price::class);
        try {
            $filters = json_decode(request()->input('filters', '[]'), true);
            $data = $dataProvider->get($filters);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceUpdateRequest $request, Price $price)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        //
    }
}
