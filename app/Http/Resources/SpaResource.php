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
            'value' => round((float) $this->value, 2),
            'percentage' => round((float) $this->percentage, 2),
            'price_plan' => $this->pricePlan,
            'rule' => $this->rule,
            'group' => $this->group,
            'operation' => $this->operation,
            'user' => $this->user,
        ];
    }
}
