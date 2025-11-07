<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteApprovalResource extends JsonResource
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
            'approver' => new UserResource($this->approver),
            'blocker' => new UserResource($this->blocker),
            'quote' => new QuoteResource($this->quote),
        ];
    }
}
