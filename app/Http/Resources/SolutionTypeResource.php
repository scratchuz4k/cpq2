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

        $tenant = $this->resource->tenant();
        $info = $tenant ? $this->resource->tenant()->pivot->toArray() : [];

        $solutionType = null;
        foreach (SolutionTypesEnum::cases() as $case) {
            if ($case->value == $this->code) {
                $solutionType = $case;
                break;
            }
        }

        $code = $solutionType ? $solutionType->getName() : 'Mobile';
        $steps = SolutionTypesEnum::getSteps()[$code] ?? [];
        $fileName = (! empty($steps) && file_exists(app_path("Helpers/Tables/$solutionType->name.php")))
            ? "$solutionType->name.php"
            : 'Default.php';
        $file = app_path("Helpers/Tables/$fileName");
        $columns = file_exists($file) ? require $file : [];

        return [
            ...$this->resource->toArray(),
            ...$info,
            'configurations' => $this->configs,
            'table' => ['columns' => $columns],
            'steps' => $this->steps,
        ];
    }
}
