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
                $this->orderItemService->store([...$order_item, 'order_id' =>  $order->id]);
            }
        }

        return $order->refresh();
    }

    public function update(Order $order, array $array): Order
    {
        if (
            array_key_exists('user_id', $array)
            && !is_null($array['user_id'])
            && !is_null($order->user_id)
            && $order->user_id !== $array['user_id']
        ) {
            throw new \Exception('Order already assigned to another user');
        }
        $order->update($array);
        if (array_key_exists('items', $array)) {
            $olderItems = collect($order->items);
            foreach ($array['items'] as $order_item) {
                if (array_key_exists('id', $order_item) && !is_null($order_item['id'])) {
                    $temp = OrderItem::find($order_item['id']);
                    $this->orderItemService->update($temp, [...$order_item, 'order_id' =>  $order->id]);

                    $olderItems = $olderItems->reject(function ($temp) use ($order_item) {
                        return $temp->id === $order_item['id'];
                    });
                } else {
                    $this->orderItemService->store([...$order_item, 'order_id' =>  $order->id]);
                }
            }
            foreach ($olderItems as $temp) {
                $temp->delete();
            }
        }

        return $order->refresh();
    }
}
