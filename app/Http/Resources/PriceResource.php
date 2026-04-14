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
            'net' => number_format((float) $this->net, config('currency.precision'), '.', ''),
            'cost' => number_format((float) $this->cost, config('currency.precision'), '.', ''),
            'net_spa' => number_format((float) $this->net_spa, config('currency.precision'), '.', ''),
            'active_spas' => SpaResource::collection($this->active_spas)->resolve(),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
