<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Services\OrderItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderItemCreateRequest;
use App\Http\Requests\OrderItemUpdateRequest;
use App\Http\Resources\OrderItemResource;

class OrderItemController extends BaseController
{
    protected $model = OrderItem::class;
    public function __construct(protected OrderItemService $orderItemService)
    {
        parent::__construct();
    }
    /**
     * List OrderItems from a specific Order
     */
    public function index()
    {
        //
    }

    /**
     * Store a new OrderItem
     */
    public function store(OrderItemCreateRequest $request)
    {
        try {
            $orderItem = DB::transaction(function () use ($request) {
                return $this->orderItemService->store($request->all());
            });
        } catch (\Exception $e) {
            if (!env("APP_DEBUG")) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return response()->json($orderItem);
    }

    /**
     * Show OrderItem
     */
    public function show(OrderItem $orderItem)
    {
        return new OrderItemResource($orderItem);
    }

    /**
     * Update OrderItem
     */
    public function update(OrderItemUpdateRequest $request, OrderItem $orderItem)
    {
        try {
            $orderItem = DB::transaction(function () use ($request, $orderItem) {
                return $this->orderItemService->update($orderItem, $request->all());
            });
        } catch (\Exception $e) {
            if (!env("APP_DEBUG")) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return response()->json($orderItem);
    }

    /**
     * Remove OrderItem
     */
    public function destroy(OrderItem $orderItem)
    {
        return $orderItem->delete();
    }
}
