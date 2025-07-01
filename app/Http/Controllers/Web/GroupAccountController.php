<?php

namespace App\Http\Controllers\Web;

use App\Export\Export;
use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupAccountCreateRequest;
use App\Http\Resources\GroupAccountResource;
use App\Http\Resources\GroupResource;
use App\Models\GroupAccount;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class GroupAccountController extends BaseController
{

    protected $model = GroupAccount::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupAccountCreateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GroupAccount $groupAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroupAccount $groupAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroupAccount $groupAccount)
    {
        //
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

        $data = $data->map(function ($item) {
            return [
                'id' => $item->id ?? null,
                'group' => $item->group?->name ?? null,
                'account' => $item->account?->name ?? null,
                'created_at' => $item->created_at ?? null,
                'updated_at' => $item->updated_at ?? null,
            ];
        });

        $date = now()->format('Y-m-d');
        $model_name = class_basename($this->model);
        $filename = "Export_{$model_name}_{$date}.xlsx";

        try {
            if (!$data->count()) {
                throw new \Exception('No data Found!');
            }
            return Excel::download(new Export($this->model, new Collection($data)), $filename);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(null, 204);
        }
    }
}
