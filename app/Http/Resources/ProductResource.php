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
            'price_plan' => $this->pricePlan,
            'price' => PriceResource::make($this->activePrice())->resolve(),
            'prices' => PriceResource::collection($this->whenLoaded('prices', default: collect([])))->resolve(),
            'categories' => CategoryResource::collection($this->whenLoaded('categories', default: collect([])))->resolve(),
            'steps' => StepResource::collection($this->whenLoaded('steps', default: collect([])))->resolve(),
            'parents' => $this->parents,
            'children' => $this->children,
            'addons' => $this->addons,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
