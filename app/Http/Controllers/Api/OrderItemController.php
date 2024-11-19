<?php

namespace App\Http\Controllers\Api;

use App\Models\QuoteItem;
use App\Services\QuoteItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteItemCreateRequest;
use App\Http\Requests\QuoteItemUpdateRequest;
use App\Http\Resources\QuoteItemResource;

class OrderItemController extends BaseController
{
    protected $model = QuoteItem::class;
    public function __construct(protected QuoteItemService $quoteItemService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteItemCreateRequest $request)
    {
        try {
            $quoteItem = DB::transaction(function () use ($request) {
                return $this->quoteItemService->store($request->all());
            });
        } catch (\Exception $e) {
            if (!env("APP_DEBUG")) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return response()->json($quoteItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuoteItem $quoteItem)
    {
        return new QuoteItemResource($quoteItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteItemUpdateRequest $request, QuoteItem $quoteItem)
    {
        try {
            $quoteItem = DB::transaction(function () use ($request, $quoteItem) {
                return $this->quoteItemService->update($quoteItem, $request->all());
            });
        } catch (\Exception $e) {
            if (!env("APP_DEBUG")) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return response()->json($quoteItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuoteItem $quoteItem)
    {
        return $quoteItem->delete();
    }
}
