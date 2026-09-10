<?php

namespace App\Http\Controllers\Web;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Models\GroupUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GroupUserController extends BaseController
{
    protected $model = GroupUser::class;

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GroupUser $groupUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroupUser $groupUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroupUser $groupUser)
    {
        //
    }

    protected function customFilter(DataProvider &$dataProvider)
    {
        $dataProvider->builder = $dataProvider->builder->whereHas('user');
    }

    protected function transformExportData(Collection $data): Collection
    {
        return new Collection($data->map(fn ($item) => [
            'id' => $item->id ?? null,
            'group' => $item->group?->name ?? null,
            'user' => $item->user?->name ?? null,
            'created_at' => $item->created_at ?? null,
            'updated_at' => $item->updated_at ?? null,
        ]));
    }
}
