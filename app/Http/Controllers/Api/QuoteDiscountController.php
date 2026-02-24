<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\QuoteDiscountCreateRequest;
use App\Http\Requests\QuoteDiscountUpdateRequest;
use App\Http\Resources\StepResource;
use App\Models\QuoteDiscount;
use App\Services\QuoteDiscountService;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class QuoteDiscountController extends BaseController
{
    protected $model = QuoteDiscount::class;

    public function __construct(protected QuoteDiscountService $service)
    {
        parent::__construct();
    }

    /**
     * List Steps
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<StepResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider($this->model);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return JsonResource::collection($data);
    }

    /**
     * Store a new Step
     */
    public function store(QuoteDiscountCreateRequest $request)
    {
        try {
            $quoteItem = DB::transaction(fn () => $this->service->store($request->all()), 2);
        } catch (Exception $e) {
            return handleException($e);
        }

        return new JsonResource($quoteItem);
    }

    /**
     * Show Step
     */
    public function show(QuoteDiscount $quoteDiscount) {}

    /**
     * Update Step
     */
    public function update(QuoteDiscountUpdateRequest $request, QuoteDiscount $quoteDiscount)
    {
        try {
            $step = DB::transaction(fn () => $this->service->update($quoteDiscount, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new StepResource($step);
    }

    /**
     * Remove Step
     */
    public function destroy(QuoteDiscount $quoteDiscount)
    {
        $quoteDiscount->delete();
    }
}
