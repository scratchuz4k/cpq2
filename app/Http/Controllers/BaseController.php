<?php

namespace App\Http\Controllers;

use App\Export\Export;
use App\Helpers\DataProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        ];
    }

    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'create', 'store', 'export'];
    }
}
