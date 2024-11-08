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
        return [
            ...$this->resource->toArray(),
            'net' => (float)$this->net,
            'net_monthly' => (float)$this->net_monthly,
            'tax_rate' => (float)$this->tax_rate,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
