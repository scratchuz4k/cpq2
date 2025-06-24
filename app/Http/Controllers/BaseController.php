<?php

namespace App\Http\Controllers;

use App\Export\Export;
use App\Helpers\DataProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
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
        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        $date = now()->format('Y-m-d');
        $model_name = class_basename($this->model);
        $filename = "Export_{$model_name}_{$date}.xlsx";

        try {
            if (!$data->count()) {
                throw new \Exception('No data Found!');
            }
            return Excel::download(new Export($this->model, $data), $filename);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(null, 204);
        }
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

