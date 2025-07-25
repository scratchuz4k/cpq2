<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolutionTypeTenantResource extends JsonResource
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
            'is_active' => (bool) $this->is_active,
            'solution_type' => (new SolutionTypeResource($this->solutionType))->resolve(),
        ];
    }
}
