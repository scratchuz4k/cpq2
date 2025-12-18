<?php

namespace App\Services;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Jobs\ImportJob;
use App\Models\Import;
use App\Models\Resource;

class ImportService
{
    public function store(array $array): Import
    {
        $file = array_key_exists('file', $array) ? $array['file'] : null;
        $overwrite = array_key_exists('overwrite', $array) ? $array['overwrite'] : 0;
        $user = auth()->user();

        if ($path = $file->store('imports', 'public')) {
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
            'model' => ImportsEnum::tryFromName($array['model'])->name ?? ImportsEnum::Product->name,
            'user_id' => $user->id,
            'overwrite' => $overwrite,
            'tenant_id' => $user->tenant_id,
        ]);

        ImportJob::dispatch($import, $import->overwrite);

        return $import;
    }

    public function preview(array $array)
    {
        $file = array_key_exists('file', $array) ? $array['file'] : null;
        $overwrite = array_key_exists('overwrite', $array) ? $array['overwrite'] : 0;
        $model = array_key_exists('model', $array) ? $array['model'] : 0;
        $user = auth()->user();
        $helperFile = require app_path('Helpers/Import/'.mb_strtolower($model).'_import.php');

        $importClass = ImportsEnum::tryFromName($model)->value;
        $importHandler = new $importClass($overwrite);

        $results = $importHandler->validateFile($file, $overwrite);

        return [
            'model' => $model,
            'overwrite' => $overwrite,
            'results' => $results,
            'user' => $user,
            'columns' => $helperFile['columns'],
        ];
    }
}
