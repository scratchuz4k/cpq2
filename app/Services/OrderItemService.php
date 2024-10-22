<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemService
{

    public function store(array $array): OrderItem
    {
        return DB::transaction(function () use ($array) {
            $orderItem = OrderItem::create($array);

            $orderItem->order->update(['status' => get_order_status($orderItem->order)]);
            return $orderItem;
        });
    }

    public function update(OrderItem $orderItem, array $array): OrderItem
    {
        return DB::transaction(function () use ($array, $orderItem) {
            $orderItem->update($array);

            $orderItem->order->update(['status' => get_order_status($orderItem->order)]);
            return $orderItem;
        });
    }
}
