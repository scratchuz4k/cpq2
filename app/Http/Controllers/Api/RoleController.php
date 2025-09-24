<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Models\Role;
use App\Services\RoleService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        $dataProvider->builder = $dataProvider->builder->where('name', '!=', 'Super Admin');
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
    public function store(Request $request)
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
    public function update(Request $request, Role $role)
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
