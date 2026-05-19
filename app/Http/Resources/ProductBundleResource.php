<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBundleResource extends JsonResource
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
            'item' => ProductResource::make($this->item->load(['categories', 'prices', 'steps']))->resolve(),
            'product' => $this->product->load(['categories']),
        ];
    }
}
