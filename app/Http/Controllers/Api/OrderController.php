<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    protected $model = Order::class;

    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }

    /**
     * List Orders
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<OrderResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Order::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return OrderResource::collection($data);
    }

    /**
     * Store a new Order
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = DB::transaction(fn () => $this->orderService->store($request->all()));
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response()->json(new OrderResource($order->refresh()));
    }

    /**
     * Show Order
     */
    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    /**
     * Update Order
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            $order = DB::transaction(fn () => $this->orderService->update($order, $request->all()));
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }

            return response(['message' => $e->getMessage()], 500);
        }

        return response()->json(new OrderResource($order->refresh()));
    }

    /**
     * Remove Order
     */
    public function destroy(Order $order)
    {
        $order->delete();
    }
}
