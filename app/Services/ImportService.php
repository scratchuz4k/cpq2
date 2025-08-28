<?php

namespace App\Services;

use App\Enums\ImportStatusesEnum;
use App\Models\Import;
use App\Models\Resource;

class ImportService
{
    public function store(array $array): Import
    {
        $file = array_key_exists('file', $array) ? $array['file'] : null;
        $override = array_key_exists('overwrite', $array) ? $array['overwrite'] : 0;
        $user = auth()->user();

        if ($path = $file->store('files', 'public')) {
            $resource = Resource::create([
                'title' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'route' => $path,
                'size' => $file->getSize(),
                'tenant_id' => $user->tenant_id,
            ]);
        }

        $import = Import::create([
            'name' => $resource->title ?? '',
            'file_id' => $resource->id ?? null,
            'status' => ImportStatusesEnum::IN_PROGRESS->name,
            'user_id' => $user->id,
            'override' => $override,
            'tenant_id' => $user->tenant_id,
        ]);

        return $import;
    }
}
