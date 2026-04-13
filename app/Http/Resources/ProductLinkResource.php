<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            ...$this->resource->toArray(),
            'child' => ProductResource::make($this->child->load(['categories', 'steps']))->resolve(),
            'product' => $this->product->load(['categories']),
        ];
    }
}
