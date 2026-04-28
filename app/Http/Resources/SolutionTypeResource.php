<?php

namespace App\Http\Resources;

use App\Enums\SolutionTypesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolutionTypeResource extends JsonResource
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

        $solutionType = null;
        foreach (SolutionTypesEnum::cases() as $case) {
            if ($case->value == $this->code) {
                $solutionType = $case;
                break;
            }
        }

        $code = $solutionType ? $solutionType->getName() : 'Mobile';
        $steps = SolutionTypesEnum::getSteps()[$code] ?? [];
        $info = $this->resource->tenant() ? $this->resource->tenant()->pivot->toArray() : [];

        return [
            ...$this->resource->toArray(),
            ...$info,
            'configurations' => $this->configs,
            'steps' => $this->steps,
        ];
    }
}
