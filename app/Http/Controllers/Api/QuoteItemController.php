<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\QuoteItemCreateRequest;
use App\Http\Requests\QuoteItemUpdateRequest;
use App\Http\Resources\QuoteItemResource;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\QuoteItemService;
use Exception;
use Illuminate\Support\Facades\DB;

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
        $quote = Quote::find($request->quote_id);
        if ($quote->isBlocked()) {
            return handleBlockedQuote();
        }

        try {
            $quoteItem = DB::transaction(fn () => $this->quoteItemService->store($request->all()), 2);
        } catch (Exception $e) {
            return handleException($e);
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
        if ($quoteItem->quote->isBlocked()) {
            return handleBlockedQuote();
        }
        try {
            $quoteItem = DB::transaction(fn () => $this->quoteItemService->update($quoteItem, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new QuoteItemResource($quoteItem);
    }

    /**
     * Remove Quote Item
     */
    public function destroy(QuoteItem $quoteItem)
    {
        if ($quoteItem->quote->isBlocked()) {
            return handleBlockedQuote();
        }

        $quote = $quoteItem->quote;
        $descendants = ! is_null($quoteItem->product_id)
            ? Product::descendants($quoteItem->product_id)
            : collect();

        $quoteItem->delete();

        // delete all items with descendant products
        if ($descendants->isNotEmpty()) {
            $quote->items()
                ->whereIn('product_id', $descendants->pluck('id'))
                ->where('data->type', '!=', 'addon_product') // keep addons, they will be deleted by cascade if parent is deleted
                ->delete();
        }

        $addons = ! is_null($quoteItem->product_id)
            ? Product::find($quoteItem->product_id)->addons
            : collect();

        // delete all items with descendant products
        if ($addons->isNotEmpty()) {
            $quote->items()
                ->whereIn('product_id', $addons->pluck('id'))
                ->where('data->type', '!=', 'addon_product') // keep addons, they will be deleted by cascade if parent is deleted
                ->delete();
        }

        $quote->computeTotals();

        return response(null, 200);
    }
}
