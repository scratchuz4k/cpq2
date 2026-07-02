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
        $productId = $quoteItem->product_id;
        $descendantIds = ! is_null($productId)
            ? Product::descendants($productId)->pluck('id')
            : collect();

        $quoteItem->delete();

        // TODO: We need to refactor this so we don't erase all ids from the quote, as some of them could be added by
        // the user without been assocatiated to the main product

        // get all descendant products
        $cascadeProductIds = collect($descendantIds);

        // get addons from selected product
        if (! is_null($productId)) {
            $product = Product::find($productId);
            if (! is_null($product)) {
                $cascadeProductIds = $cascadeProductIds->merge($product->addonsChildren->pluck('id'));
            }
        }

        // get addons from descendants
        if ($descendantIds->isNotEmpty()) {
            $descendantProducts = Product::query()
                ->with('addons:id')
                ->whereIn('id', $descendantIds)
                ->get();

            foreach ($descendantProducts as $descendantProduct) {
                $cascadeProductIds = $cascadeProductIds->merge($descendantProduct->addons->pluck('id'));
            }
        }

        $cascadeProductIds = $cascadeProductIds->unique()->values();

        // delete related products from quote
        if ($cascadeProductIds->isNotEmpty()) {
            foreach ($quote->items->whereIn('product_id', $cascadeProductIds) as $item) {
                $item->delete();
            }
        }

        return response(null, 200);
    }
}
