<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\Role;
use App\Services\RoleService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * @response AnonymousResourceCollection<LengthAwarePaginator<JsonResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider($this->model);
        $dataProvider->builder = $dataProvider->builder->where('roles.name', '!=', 'Super Admin');
        $dataProvider->builder = $dataProvider->builder->where('roles.tenant_id', Auth::user()->tenant_id);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return JsonResource::collection($data);
    }

    /**
     * Store a new role
     */
    #[ExcludeRouteFromDocs]
    public function store(RoleCreateRequest $request)
    {
        try {
            $role = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new JsonResource($role);
    }

    /**
     * Show role
     */
    #[ExcludeRouteFromDocs]
    public function show(Role $role)
    {
        return new JsonResource($role);
    }

    /**
     * Update role
     */
    #[ExcludeRouteFromDocs]
    public function update(RoleUpdateRequest $request, Role $role)
    {
        try {
            $role = DB::transaction(fn () => $this->service->update($role, $request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new JsonResource($role);
    }

    /**
     * Remove role
     */
    public function destroy(Role $role)
    {
        $role->delete();
    }
}
