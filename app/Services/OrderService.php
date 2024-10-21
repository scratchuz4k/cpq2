<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{

    public function __construct(protected OrderItemService $orderItemService) {}
    public function store(array $array): Order
    {
        return DB::transaction(function () use ($array) {
            $order = Order::create($array);

            if (array_key_exists('items', $array)) {
                foreach ($array['items'] as $order_item) {
                    $order_item['order_id'] = $order->id;
                    $this->orderItemService->store($order_item);
                }
            }

            return $order;
        });
    }

    public function update(Order $order, array $array): Order
    {
        return DB::transaction(function () use ($order, $array) {
            $order->update(
                [
                    ...$array,
                    'name' => ''
                ]
            );

            if (array_key_exists('items', $array)) {
                foreach ($array['items'] as $order_item) {
                    $order_item['order_id'] = $order->id;
                    if ($temp = OrderItem::find($order_item['id'])) {
                        $this->orderItemService->update($temp, $order_item);
                        continue;
                    }
                    $this->orderItemService->store($order_item);
                }
            }

            return $order;
        });
    }
}
