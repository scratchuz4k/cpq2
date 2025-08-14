<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = str_replace("App\Models\\", '', $this->activitable_type);
        $file = $item.'Resource';
        $temp = null;
        if (file_exists(app_path("Http/Resources/$file.php"))) {
            $resource = 'App\\Http\\Resources\\'.$file;
            $temp = $resource::make($this->activitable);
        }

        return [
            ...$this->resource->toArray(),
            'activitable_type' => str_replace("App\Models\\", '', $this->activitable_type),
            'user' => $this->user,
        ];
    }
}
