<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends BaseController
{
    protected $model = Order::class;
    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<OrderResource>>
     */
    public function index()
    {
        $dataProvider = new DataProvider(Order::class);

        $user = request()->user();
        $users = collect([]);
        if (!$user->hasRole('Super Admin')) {
            $userDescedents = collect([$user]);
            do {
                $current = $userDescedents->pop();
                $users->push($current->id);
                $userDescedents->push(...$current->children);
            } while (count($userDescedents));
            $dataProvider->builder = $dataProvider->builder->whereIn('user_id', $users);
        }
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return OrderResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = $this->orderService->store($request->all());
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
        return response()->json(new OrderResource($order));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            $order = $this->orderService->update($order, $request->all());
        } catch (Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }

        return response()->json(new OrderResource($order));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
    }
}
