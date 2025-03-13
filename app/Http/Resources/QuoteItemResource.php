<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'margin' => round((float)$this->margin, 4),
            'discount' => round((float)$this->discount, 4),
            'raw_price' => round((float)$this->raw_price, 4),
            'original_price' => round((float)$this->original_price, 4),
            'price' => round((float)$this->price, 4),
            'total' => round((float)$this->total, 4),
            'total_tax' => round((float)$this->total_tax, 4),
            'total_gross' => round((float)$this->total_gross, 4),
            'tax_order_item' => round((float)$this->tax_order_item, 4),
            'price_plan' => $this->pricePlan,
            'contract_length' => (float)$this->contract_length,
        ];
    }
}
