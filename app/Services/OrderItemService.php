<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemService
{

    public function store(array $array): OrderItem
    {
        return DB::transaction(function () use ($array) {
            $orderItem = OrderItem::create([
                ...$array,
                'tax_order_item' => 20,
                'original_price' => $array['price'],
                'original_price_monthly' => $array['price_monthly'],
            ]);

            return $orderItem;
        });
    }

    public function update(OrderItem $orderItem, array $array): OrderItem
    {
        return DB::transaction(function () use ($array, $orderItem) {
            $orderItem->update($array);

            $orderItem->computeTotals();
            $orderItem->order->computeTotals();
            $orderItem->order->update(['status' => get_order_status($orderItem->order)]);
            return $orderItem;
        });
    }
}
