<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteDiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $entity1 = app($this->discountable_type)::where('id', $this->discountable_id)->first();

        return [
            ...$this->resource->toArray(),
            'discount' => (float)$this->discount,
            'entity' => $entity1,
        ];
    }
}
