<?php

namespace App\Http\Controllers;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Export\Export;
use App\Export\ExportCSVTemplate;
use App\Helpers\DataProvider;
use App\Imports\Import;
use App\Jobs\ImportJob;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $model;

    public function __construct()
    {
        if ($this->model) {
            $this->authorizeResource($this->model);
        }
    }

    public function export(Request $request)
    {
        $dataProvider = new DataProvider($this->model);

        $this->customFilter($dataProvider);

        try {
            $data = $dataProvider->getCollection();
        } catch (\Exception $e) {
            return handleException($e);
        }

        $data = $this->transformExportData($data);

        $date = now()->format('Y-m-d');
        $model_name = class_basename($this->model);
        $filename = "Export_{$model_name}_{$date}.xlsx";

        try {
            if (! $data->count()) {
                return handleException(new \Exception, 'No data Found!');
            }

            return Excel::download(new Export($this->model, $data), $filename);
        } catch (\Exception $e) {
            return handleException($e);
        }
    }

    public function import(Request $request)
    {
        if ($request->isMethod('get')) {
            $model = Str::lower(class_basename($this->model));
            $template = app_path("Helpers/Import/{$model}_import.php");
            if (file_exists($template)) {
                $template = require $template;

                return Excel::download(
                    new ExportCSVTemplate($this->model, collect($template['data'])),
                    $template['filename']
                );
            }

            $data = $this->model::factory(10)->make();

            return Excel::download(
                new ExportCSVTemplate($this->model, $data),
                Str::lower(class_basename($this->model).'_import_template.csv')
            );
        }

        // TODO: Use custom import when it exists

        $file = array_key_exists('file', $request->all()) ? $request['file'] : null;
        $isDryRun = array_key_exists('dry_run', $request->all()) ? $request['dry_run'] : null;
        $user = auth()->user();

        $importClass = ImportsEnum::importClassFor(class_basename($this->model));
        $importHandler = new $importClass(auth()->user(), $this->model, $isDryRun);

        $results = $importHandler->validateFile($file, $this->model, $user);

        if (get_class($importHandler) !== Import::class) {
            $helperFile = require app_path('Helpers/Import/'.mb_strtolower(class_basename($this->model)).'_import.php');
            $columns = $helperFile['columns'];
        } else {
            $columns = array_combine(
                getFillableColumns($this->model),
                array_map(fn ($key) => Str::headline($key), getFillableColumns($this->model))
            );
        }

        if (! $isDryRun) {
            $filename = Str::random(40).'.'.$file->getClientOriginalExtension();
            if ($path = $file->storeAs('imports', $filename, 'public')) {
                $resource = Resource::create([
                    'title' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'route' => $path,
                    'size' => $file->getSize(),
                    'tenant_id' => $user->tenant_id,
                ]);
            }

            $import = \App\Models\Import::create([
                'name' => $file->getClientOriginalName(),
                'file_id' => $resource->id ?? null,
                'status' => ImportStatusesEnum::IN_PROGRESS->name,
                'model' => class_basename($this->model),
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);

            $import->model = $this->model;
            ImportJob::dispatch($import, $user);

            return $import;
        }

        return [
            'results' => $results,
            'user' => $user,
            'columns' => $columns,
        ];
    }

    protected function transformExportData(Collection $data): Collection
    {
        return $data;
    }

    protected function resourceAbilityMap()
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'view',
            'update' => 'update',
            'destroy' => 'delete',
            'export' => 'export',
            'import' => 'import',
        ];
    }

    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'create', 'store', 'export', 'import'];
    }

    protected function customFilter(DataProvider &$dataProvider)
    {
        return $dataProvider;
    }
}
