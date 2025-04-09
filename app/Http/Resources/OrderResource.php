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
            'margin' => (float) $this->margin,
            'discount' => (float) $this->discount,
            'commission_rate' => (float) $this->commission_rate,
            'contract_length' => (float) $this->contract_length,
            'user' => $this->user,
            'account' => $this->account,
            'solution_type' => (new SolutionTypeResource($this->solutionType))->resolve(),
            'items' => OrderItemResource::collection($this->whenLoaded('items', default: collect([])))->resolve(),
            'updated_at' => $this->updated_at,
        ];
    }
}
