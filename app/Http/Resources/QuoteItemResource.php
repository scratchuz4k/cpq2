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
            'quantity' => (float)$this->quantity,
            'margin' => (float)$this->margin,
            'discount' => (float)$this->discount,
            'raw_price' => (float)$this->raw_price,
            'original_price' => (float)$this->original_price,
            'price' => (float)$this->price,
            'total' => (float)$this->total,
            'total_tax' => (float)$this->total_tax,
            'total_gross' => (float)$this->total_gross,
            'tax_order_item' => (float)$this->tax_order_item,
            'price_plan' => $this->pricePlan,
            'contract_length' => (float)$this->contract_length,
            'children' => QuoteItemResource::collection($this->children)
        ];
    }
}
