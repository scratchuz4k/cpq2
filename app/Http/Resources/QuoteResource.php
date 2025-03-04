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
            'margin' => round((float)$this->margin, 4),
            'discount' => round((float)$this->discount, 4),
            'commission_rate' => round((float)$this->commission_rate, 2),
            'user' => $this->user,
            'account' => $this->account,
            'solution_type' => (new SolutionTypeResource($this->solutionType))->resolve(),
            'shared_link' => (new SharedLinkResource($this->sharedLink))->resolve(),
            'discounts' => QuoteDiscountResource::collection($this->discounts)->resolve(),
            'items' => QuoteItemResource::collection($this->items)->resolve(),
            'orders' => $this->whenLoaded('orders'),
            'updated_at' => $this->updated_at,
        ];
    }
}
