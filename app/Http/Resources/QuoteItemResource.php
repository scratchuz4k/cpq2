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
            'discount' => (float) $this->discount,
            'margin' => format_currency($this->margin),
            'raw_price' => format_currency($this->raw_price),
            'original_price' => format_currency($this->original_price),
            'price' => format_currency($this->price),
            'total' => format_currency($this->total),
            'total_tax' => format_currency($this->total_tax),
            'total_gross' => format_currency($this->total_gross),
            'price_plan' => $this->pricePlan,
            'renewal_terms_value' => $this->renewal_terms_value,
            'renewal_terms_unit' => $this->renewal_terms_unit,
            'billing_frequency_value' => $this->billing_frequency_value,
            'billing_frequency_unit' => $this->billing_frequency_unit,
            'number_of_payments' => $this->numberOfPayments(),
            'is_deleted' => $this->trashed(),
            'is_margin_valid' => $this->isWithinMarginThresholds(),
        ];
    }
}
