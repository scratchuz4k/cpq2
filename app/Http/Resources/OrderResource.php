<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'margin' => format_currency($this->margin),
            'discount' => format_currency($this->discount),
            'commission_rate' => format_currency($this->commission_rate),
            'contract_length' => format_currency($this->contract_length),
            'user' => $this->user,
            'account' => $this->account,
            'solution_type' => $this->solutionType,
            'items' => OrderItemResource::collection($this->whenLoaded('items', default: collect([])))->resolve(),
            'updated_at' => $this->updated_at,
        ];
    }
}
