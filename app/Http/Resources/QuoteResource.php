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
            'user' => $this->user,
            'total_vat' => $this->total * 0.2,
            'total_gross' => $this->total * 1.2,
            'account' => $this->account,
            'solution_type' => $this->solutionType,
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            'updated_at' => $this->updated_at,
            'orders' => QuoteResource::collection($this->whenLoaded('orders'))
        ];
    }
}
