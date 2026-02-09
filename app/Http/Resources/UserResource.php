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

            'group' => $this->group(),
            'roles' => $this->whenLoaded('roles'),
            'groups' => $this->whenLoaded('groups'),
            'current_tenant' => $this->whenLoaded('currentTenant'),
            'tenants' => $this->whenLoaded('tenants'),

            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
