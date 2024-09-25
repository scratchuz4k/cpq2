<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    private $solution_types = [
        3 => 'Connectivity - Ethernet & Fibre',
        4 => 'Mobile',
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
            'user' => $this->user,
            'account' => $this->account,
            'solution_type' => $this->solutionType,
            'items' => $this->items,
            'totals' => $this->totals(),
            'updated_at' => format_date($this->updated_at),
        ];
    }
}
