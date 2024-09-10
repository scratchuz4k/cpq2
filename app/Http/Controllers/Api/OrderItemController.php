<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderItemCreateRequest;

class OrderItemController extends BaseController
{
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
    public function store(OrderItemCreateRequest $request)
    {
        try {
            $orderItem = DB::transaction(function () use ($request) {
                $orderItem = OrderItem::create([
                    ...$request->all(),
                    "tenant_id" => app("currentTenant")->id,
                ]);

                return $orderItem;
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
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem)
    {
        return $orderItem->delete();
    }
}
