<?php

namespace App\Http\Controllers\Web;

use App\Export\Export;
use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupAccountCreateRequest;
use App\Models\GroupAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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
        } catch (\Exception $e) {
            return handleException($e);
        }

        $data = $data->map(fn ($item) => [
            'id' => $item->id ?? null,
            'group' => $item->group?->name ?? null,
            'account' => $item->account?->name ?? null,
            'created_at' => $item->created_at ?? null,
            'updated_at' => $item->updated_at ?? null,
        ]);

        $date = now()->format('Y-m-d');
        $model_name = class_basename($this->model);
        $filename = "Export_{$model_name}_{$date}.xlsx";

        try {
            if (! $data->count()) {
                return handleException(new \Exception, 'No data Found!');
            }

            return Excel::download(new Export($this->model, new Collection($data)), $filename);
        } catch (\Exception $e) {
            return handlException($e);
        }
    }

    protected function transformExportData(Collection $data): Collection
    {
        return new Collection($data->map(fn ($item) => [
            'id' => $item->id ?? null,
            'group' => $item->group?->name ?? null,
            'account' => $item->account?->name ?? null,
            'created_at' => $item->created_at ?? null,
            'updated_at' => $item->updated_at ?? null,
        ]));
    }
}
