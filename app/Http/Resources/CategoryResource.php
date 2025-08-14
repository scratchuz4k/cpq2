<?php

namespace App\Http\Resources;

use App\Models\SolutionType;
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
            'solution_type' => SolutionTypeTenantResource::make(SolutionType::find($this->solution_type_id))->resolve(),
        ];
    }
}
