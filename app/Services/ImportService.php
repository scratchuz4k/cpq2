<?php

namespace App\Services;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Export\ExportCSVTemplate;
use App\Jobs\ImportJob;
use App\Models\Import;
use App\Models\Resource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ImportService
{
    public function store(array $array): Import
    {
        $file = $array['file'] ?? null;
        $overwrite = array_key_exists('overwrite', $array) ? $array['overwrite'] : 0;
        $user = auth()->user();

        if (! $file instanceof UploadedFile) {
            throw new RuntimeException('No import file was provided.');
        }

        // The "public" disk is configured with throw => false, so a failed write
        // returns false instead of raising — bail out loudly rather than creating
        // an Import row that points at a file that was never stored.
        $path = $file->store('imports', 'public');

        if ($path === false) {
            throw new RuntimeException('The import file could not be stored.');
        }

        $resource = Resource::create([
            'title' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'route' => $path,
            'size' => $file->getSize(),
            'tenant_id' => $user->tenant_id,
        ]);

        $import = Import::create([
            'name' => $resource->title,
            'file_id' => $resource->id,
            'status' => ImportStatusesEnum::IN_PROGRESS->name,
            'model' => ImportsEnum::tryFromName($array['model'])->name ?? ImportsEnum::Product->name,
            'user_id' => $user->id,
            'overwrite' => $overwrite,
            'tenant_id' => $user->tenant_id,
        ]);

        ImportJob::dispatch($import, $user, $import->overwrite);

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
        $importHandler = new $importClass($user, true);

        $results = $importHandler->validateFile($file, $user, $overwrite);

        return [
            'model' => $model,
            'overwrite' => $overwrite,
            'results' => $results,
            'user' => $user,
            'columns' => $helperFile['columns'],
        ];
    }

    public function getImportTemplate(array $array, $modelClass)
    {
        $model = Str::lower(class_basename($modelClass));
        $template = app_path("Helpers/Import/{$model}_import.php");
        if (file_exists($template)) {
            $template = require $template;

            return Excel::download(
                new ExportCSVTemplate($modelClass, collect($template['data']), $template['columns'] ?? null),
                $template['filename']
            );
        }

        $data = $modelClass::factory(10)->make();

        return Excel::download(
            new ExportCSVTemplate($modelClass, $data),
            Str::lower('import_'.class_basename($modelClass).'_template.csv')
        );
    }
}
