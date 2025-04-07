<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Models\PricePlan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PricePlanController extends BaseController
{
    protected $model = PricePlan::class;

    /**
     * Index
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<PricePlanResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(PricePlan::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return JsonResource::collection($data);
    }

    /**
     * Store
     */
    public function store(Request $request) {}

    /**
     * Show.
     */
    public function show(PricePlan $pricePlan) {}

    /**
     * Update
     */
    public function update(Request $request, PricePlan $pricePlan) {}

    /**
     * Remove
     */
    public function destroy(PricePlan $pricePlan) {}
}
