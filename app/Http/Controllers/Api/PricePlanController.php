<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Models\PricePlan;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
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
     * List Price Plans
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
     * Create a new Price Plan
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
    }

    /**
     * Show Price Plan
     */
    #[ExcludeRouteFromDocs]
    public function show(PricePlan $pricePlan)
    {
    }

    /**
     * Update Price Plan
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, PricePlan $pricePlan)
    {
    }

    /**
     * Remove Price Plan
     */
    #[ExcludeRouteFromDocs]
    public function destroy(PricePlan $pricePlan)
    {
    }
}
