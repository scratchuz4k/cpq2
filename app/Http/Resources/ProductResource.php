<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\PriceResource;
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
            'price' =>  new PriceResource($this->activePrice()),
            'prices' => PriceResource::collection($this->whenLoaded("prices", default: collect([]))),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
