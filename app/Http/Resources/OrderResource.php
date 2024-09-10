<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    private $solution_types = [
        "3" => 'Connectivity - Ethernet & Fibre',
        "4" => 'Mobile',
        // "1" => 'Connectivity - Ethernet Point to Point',
        // "2" => 'Connectivity - Fibre',
    ];
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->resource->toArray(),
            'solution_type_name' => array_key_exists($this->solution_type_id, $this->solution_types) ? $this->solution_types[$this->solution_type_id] : null,
            'account' => $this->account,
            'items' => $this->items,
            'updated_at' => format_date($this->updated_at),
        ];
    }
}
