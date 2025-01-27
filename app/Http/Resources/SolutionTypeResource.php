<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Enums\SolutionTypesEnum;
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

        $code = "Mobile";
        foreach (SolutionTypesEnum::cases() as $case) {
            if ($case->value == $this->code) {
                $code = $case->name;
            };
        }

        $file = app_path("Helpers/Tables/$code.php");
        $columns = [];
        if (file_exists($file)) {
            $data = require $file;
            if (array_key_exists("component", $this->configs) && array_key_exists($this->configs["component"], $data)) {
                $columns = $data[$this->configs["component"]];
            }else{
                $columns = $data["DealMaker"];
            }
        }

        return [
            ...$this->resource->toArray(),
            "configurations" => $this->configs,
            "table" => ["columns" => $columns]
        ];
    }
}
