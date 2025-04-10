<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\OrderItemCreateRequest;
use App\Http\Requests\OrderItemUpdateRequest;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use App\Services\OrderItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderItemController extends BaseController
{
    protected $model = OrderItem::class;

    public function __construct(protected OrderItemService $orderItemService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(OrderItem::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return OrderItemResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderItemCreateRequest $request)
    {
        try {
            $orderItem = DB::transaction(function () use ($request) {
                return $this->orderItemService->store($request->all());
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return response()->json($orderItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        return new OrderItemResource($orderItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderItemUpdateRequest $request, OrderItem $orderItem)
    {
        try {
            $orderItem = DB::transaction(function () use ($request, $orderItem) {
                return $this->orderItemService->update($orderItem, $request->all());
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return response()->json($orderItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        return $orderItem->delete();
    }
}
