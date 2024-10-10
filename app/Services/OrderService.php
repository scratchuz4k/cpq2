<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function store(array $array): Order
    {
        return DB::transaction(function () use ($array) {
            $order = Order::create(
                [
                    ...$array,
                    'tenant_id' => session()->get('tenant_id'),
                ]
            );

            return $order;
        });
    }

    public function update(Order $order, array $array): Order
    {
        return DB::transaction(function () use ($order, $array) {
            $order->update(
                [
                    ...$array,
                    'tenant_id' => session()->get('tenant_id'),
                    'name' => 'WIP'
                ]
            );

            return $order;
        });
    }
}
