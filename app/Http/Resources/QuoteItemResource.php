<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class QuoteItemResource extends JsonResource
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
            'quantity' => $this->quantity,
            'margin' => Number::currency($this->margin),
            'discount' => $this->discount,
            'raw_price' => Number::currency($this->raw_price),
            'original_price' => Number::currency($this->original_price),
            'price' => Number::currency($this->price),
            'total' => Number::currency($this->total),
            'total_tax' => Number::currency($this->total_tax),
            'total_gross' => Number::currency($this->total_gross),
            'tax_order_item' => Number::currency($this->tax_order_item),
            'price_plan' => $this->pricePlan,
            'contract_length' => (float) $this->contract_length,
        ];
    }
}
