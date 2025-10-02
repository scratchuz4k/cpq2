<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\QuoteItemCreateRequest;
use App\Http\Requests\QuoteItemUpdateRequest;
use App\Http\Resources\QuoteItemResource;
use App\Models\QuoteItem;
use App\Services\QuoteItemService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteItemController extends BaseController
{
    protected $model = QuoteItem::class;

    public function __construct(protected QuoteItemService $quoteItemService)
    {
        parent::__construct();
    }

    /**
     * List of Quote Items from a Specific Quote
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(QuoteItem::class);
        try {
            return $dataProvider->group(QuoteItemResource::class);
        } catch (Exception $e) {
            return handleException($e);
        }
    }

    /**
     * Store a new Quote Item
     */
    public function store(QuoteItemCreateRequest $request)
    {
        try {
            $quoteItem = DB::transaction(fn () => $this->quoteItemService->store($request->all()), 2);
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }

            return response()->json($e->getMessage(), 500);
        }

        return new QuoteItemResource($quoteItem);
    }

    /**
     * Show Quote Item
     */
    public function show(QuoteItem $quoteItem)
    {
        return new QuoteItemResource($quoteItem);
    }

    /**
     * Update Quote Item
     */
    public function update(QuoteItemUpdateRequest $request, QuoteItem $quoteItem)
    {
        try {
            $quoteItem = DB::transaction(fn () => $this->quoteItemService->update($quoteItem, $request->all()));
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return new QuoteItemResource($quoteItem);
    }

    /**
     * Remove Quote Item
     */
    public function destroy(QuoteItem $quoteItem)
    {
        $quote = $quoteItem->quote;
        $quoteItem->delete();
        $quote->computeTotals();

        return response(null, 200);
    }
}
