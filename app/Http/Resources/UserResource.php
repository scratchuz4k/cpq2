<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'has_children' => $this->hasChildren(),
            'parent' => $this->whenLoaded('parent'),
            'roles' => $this->whenLoaded('roles'),
            'tenants' => $this->whenLoaded('tenants'),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
