<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
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
            'tokens' => $this->whenLoaded('tokens'),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
