<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'supplier' => $this->supplier,
            'brand' => $this->brand,
            'price_plan' => $this->pricePlan,
            'price' => PriceResource::make($this->activePrice())->resolve(),
            'prices' => PriceResource::collection($this->whenLoaded('prices', default: collect([])))->resolve(),
            'category' => CategoryResource::make($this->category)->resolve(),
            'parent' => ProductResource::make($this->parent)->resolve(),
            'has_children' => (bool) $this->children->count(),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
