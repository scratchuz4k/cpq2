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
                'original_price' => $array['price']
            ]);

            if (array_key_exists('children', $array)) {
                foreach ($array['children'] as $child) {
                    $childItem = OrderItem::create([
                        ...$array,
                        ...$child,
                        'parent_id' => $orderItem->id,
                        'original_price' => $child['price'] ?? 0,
                    ]);

                }
            }


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
