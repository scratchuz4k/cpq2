<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Models\Permission;
use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

#[ExcludeAllRoutesFromDocs]
class PermissionController extends BaseController
{
    protected $model = Permission::class;

    /**
     * List of roles
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<JsonResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider($this->model);
        $permissions = Auth::user()->getAllPermissions();
        $parentRole = Auth::user()->roles->count() ? Auth::user()->roles[0]->parent_id : null;
        if (! $parentRole) {
            $permissions = Permission::all();
        }
        $dataProvider->builder = $dataProvider->builder->whereIn('id', $permissions->map(fn ($x) => $x->id)->values());

        try {
            return $dataProvider->group(JsonResource::class);
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }
    }

    /**
     * Store a new Permission
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Permission
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Update Permission
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove Permission
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
