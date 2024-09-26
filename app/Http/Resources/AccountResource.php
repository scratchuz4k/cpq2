<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            "tenant" => $this->tenant,
            "contacts" => $this->contacts,
            'created_at' => format_date($this->created_at),
            'updated_at' => format_date($this->updated_at),
        ];
    }
}
