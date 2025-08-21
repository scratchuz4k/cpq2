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
            'net' => number_format((float) $this->net, config('currency.precision')),
            'cost' => number_format((float) $this->cost, config('currency.precision')),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
