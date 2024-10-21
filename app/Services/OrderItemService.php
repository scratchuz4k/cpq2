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
            return $orderItem;
        });
    }

    public function update(OrderItem $orderItem, array $array): OrderItem
    {
        return DB::transaction(function () use ($array, $orderItem) {
            $orderItem->update($array);

            return $orderItem;
        });
    }
}
