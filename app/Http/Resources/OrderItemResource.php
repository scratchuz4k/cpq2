<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->resource->toArray(),
            'quantity' => (float)$this->quantity,
            'margin' => (float)$this->margin,
            'discount' => (float)$this->discount,
            'raw_price' => (float)$this->raw_price,
            'raw_price_monthly' => (float)$this->raw_price_monthly,
            'original_price' => (float)$this->original_price,
            'original_price_monthly' => (float)$this->original_price_monthly,
            'price' => (float)$this->price,
            'price_monthly' => (float)$this->price_monthly,
            'total' => (float)$this->total,
            'total_tax' => (float)$this->total_tax,
            'total_gross' => (float)$this->total_gross,
            'total_monthly' => (float)$this->total_monthly,
            'total_monthly_tax' => (float)$this->total_monthly_tax,
            'total_monthly_gross' => (float)$this->total_monthly_gross,
            'tax_order_item' => (float)$this->tax_order_item,
            'data' => json_decode($this->data, true),
        ];
    }
}
