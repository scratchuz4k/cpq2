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
            'net' => format_currency((float) $this->net),
            'tax_rate' => format_currency((float) $this->tax_rate),
            'tax' => format_currency((float) $this->tax),
            'gross' => format_currency((float) $this->gross),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
