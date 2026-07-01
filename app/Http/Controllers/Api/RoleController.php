<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    protected $model = Role::class;

    public function __construct(protected RoleService $service)
    {
        parent::__construct();
    }

    /**
     * List of roles
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<RoleResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider($this->model);
        $dataProvider->builder = $dataProvider->builder->where('roles.name', '!=', 'Super Admin');
        $dataProvider->builder = $dataProvider->builder->where('roles.tenant_id', Auth::user()->tenant_id);
        $dataProvider->builder = $this->service->getChilds($dataProvider->builder);

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return RoleResource::collection($data);
    }

    /**
     * Store a new role
     */
    public function store(RoleCreateRequest $request)
    {
        try {
            $role = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new RoleResource($role);
    }

    /**
     * Show role
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    /**
     * Update role
     */
    public function update(RoleUpdateRequest $request, Role $role)
    {
        try {
            $role = DB::transaction(fn () => $this->service->update($role, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new RoleResource($role);
    }

    /**
     * Remove role
     */
    public function destroy(Role $role)
    {
        $role->delete();
    }
}
