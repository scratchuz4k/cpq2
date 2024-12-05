<?php

namespace App\Services;

use App\Events\OrderStatusEvent;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{

    public function __construct(protected OrderItemService $orderItemService) {}
    public function store(array $array): Order
    {
        $order = Order::create($array);

        if (array_key_exists('items', $array)) {
            foreach ($array['items'] as $order_item) {
                $order_item['order_id'] = $order->id;
                $this->orderItemService->store($order_item);
            }
        }

        return $order;
    }
}
