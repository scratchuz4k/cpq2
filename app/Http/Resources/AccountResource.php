<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $main_contact = $this->contacts[0] ?? null;

        return [
            ...$this->resource->toArray(),
            'contacts' => (ContactResource::collection($this->contacts))->resolve(),
            'main_contact' => new ContactResource($main_contact),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
