<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Http\Controllers\BaseController;
use App\Http\Resources\OrderResource;
use App\Http\Requests\OrderCreateRequest;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Order::with(['user', 'account'])->orderBy('id', 'desc')->get();
        return response()->json(["data" => OrderResource::collection($items)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = $this->orderService->store($request->all());
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
        return response()->json(new OrderResource($order));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
