<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SharedLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $date = Carbon::parse($this->expires_at);
        return [
            'id' => $this->id,
            'token' => $this->token,
            'shareable_type' => $this->shareable_type,
            'shareable_id' => $this->shareable_id,
            'expires_at' => $this->expires_at ? $date->format('d-m-Y') : null,
            'url' => $this->url,
        ];
    }
}
