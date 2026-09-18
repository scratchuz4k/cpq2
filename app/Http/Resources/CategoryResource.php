<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): ?array
    {
        if (is_null($this->resource)) {
            return null;
        }

        return [
            ...$this->resource->toArray(),
            'solution_type' => SolutionTypeTenantResource::make($this->solutionType)->resolve(),
            'parent' => CategoryResource::make($this->parent)->resolve(),
            'products' => $this->whenLoaded('products', fn () => $this->products->pluck('id')->values()),
        ];
    }
}
