<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
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
            'margin' => (float)$this->margin,
            'discount' => (float)$this->discount,
            'commission_rate' => (float)$this->commission_rate,
            'contract_length' => (float)$this->contract_length,
            'user' => $this->user,
            'total_monthly_vat' => $this->total_monthly * 0.2,
            'total_monthly_gross' => $this->total_monthly * 1.2,
            'total_vat' => $this->total * 0.2,
            'total_gross' => $this->total * 1.2,
            'account' => $this->account,
            'solution_type' => $this->solutionType,
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            // TODO: Relte order
            'updated_at' => $this->updated_at,
            'orders' => $this->orders
        ];
    }
}
