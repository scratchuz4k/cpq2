<?php

namespace App\Http\Resources\Cloudaudit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'data' => [
                'account' => $this->account(),
                'email' => $this->email(),
                'report' => $this->report(),
            ],
        ];
    }
}
