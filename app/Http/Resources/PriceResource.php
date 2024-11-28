<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
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
            'net' => round((float)$this->net,4),
            'tax_rate' => round((float)$this->tax_rate,4),
            'tax' => round((float)$this->tax,4),
            'gross' => round((float)$this->gross,4),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
