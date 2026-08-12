<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'parent' => $this->parent ? [
                ...$this->parent->toArray(),
                'permissions' => $this->parent->getAllPermissions(),
            ] : null,
            'permissions' => $this->getAllPermissions(),
            'descendant_ids' => $this->getDescendantIds(),
        ];
    }
}
