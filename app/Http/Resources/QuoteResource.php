<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Sqids\Sqids;

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
            'user' => $this->user,
            'is_blocked' => $this->isBlocked(),
            'commission_value' => format_currency($this->commission_value),
            'margin' => format_currency($this->margin),
            'total' => format_currency($this->total),
            'total_tax' => format_currency($this->total_tax),
            'total_gross' => format_currency($this->total_gross),
            'account' => (new AccountResource($this->account))->resolve(),
            'solution_type' => (new SolutionTypeResource($this->solutionType))->resolve(),
            'shared_link' => (new SharedLinkResource($this->sharedLink))->resolve(),
            'discounts' => QuoteDiscountResource::collection($this->discounts)->resolve(),
            'items' => QuoteItemResource::collection($this->items)->resolve(),
            'items_trashed' => QuoteItemResource::collection($this->itemsTrashed)->resolve(),
            'orders' => $this->whenLoaded('orders'),
            'updated_at' => $this->updated_at,
            'configs' => $this->configs,
            'approvals' => $this->approvals,
            'hash' => app(Sqids::class)->encode([
                $this->id,
                $this->tenant_id,
                $this->user_id,
            ]),
        ];
    }
}
