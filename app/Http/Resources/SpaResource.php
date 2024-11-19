<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity1 = app($this->rule->entity_1)::where('id', $this->field_1)->first();
        $entity2 = $this->rule->entity_2 ? app($this->rule->entity_2)::where('id', $this->field_2)->first() : null;
        return [
            ...$this->resource->toArray(),
            'field_1' => $entity1,
            'field_2' => $entity2,
            'value' => (float)$this->value,
            'percentage' => (float) $this->percentage,
            'rule' => $this->rule,
            'operation' => $this->operation,
            'user' => $this->user
        ];
    }
}
