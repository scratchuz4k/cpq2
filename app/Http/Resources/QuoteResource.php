<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

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
            'commission_value' => round((float) $this->commission_value, 2),
            'user' => $this->user,
            'account' => (new AccountResource($this->account))->resolve(),
            'solution_type' => (new SolutionTypeResource($this->solutionType))->resolve(),
            'shared_link' => (new SharedLinkResource($this->sharedLink))->resolve(),
            'discounts' => QuoteDiscountResource::collection($this->discounts)->resolve(),
            'items' => QuoteItemResource::collection($this->items)->resolve(),
            'orders' => $this->whenLoaded('orders'),
            'updated_at' => $this->updated_at,
            'margin' => Number::currency($this->margin),
            'total' => Number::currency($this->total),
            'total_tax' => Number::currency($this->total_tax),
            'total_gross' => Number::currency($this->total_gross),
        ];
    }
}
