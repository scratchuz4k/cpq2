<?php

namespace App\Http\Resources;

use App\Enums\ImportStatusesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
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
            'user' => $this->user,
            'status' => ImportStatusesEnum::{$this->status}->value,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
