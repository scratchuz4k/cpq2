<?php

namespace App\Http\Controllers\Web;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleController extends BaseController
{
    protected $model = Role::class;

    public function __construct(protected RoleService $service)
    {
        parent::__construct();
    }

    public function index(IndexRequest $request)
    {
        return Inertia::render(component: 'Roles/Index');
    }

    public function create(Request $request)
    {
        return Inertia::render(component: 'Roles/Create');
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
            return handleWebException($e);
        }

        return Inertia::render(component: 'Roles/Index');
    }

    /**
     * Show role
     */
    #[ExcludeRouteFromDocs]
    public function show(Role $role)
    { //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {

        return Inertia::render('Roles/Edit', [
            'role' => RoleResource::make($role)->resolve(),
        ]);
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
            return handleWebException($e);
        }

        return redirect()->route('roles.index');
    }

    /**
     * Remove role
     */
    public function destroy(Role $role)
    {//
    }

    protected function customFilter(DataProvider &$dataProvider)
    {
        $dataProvider->builder = $dataProvider->builder->where('roles.name', '!=', 'Super Admin');
        $dataProvider->builder = $dataProvider->builder->where('roles.tenant_id', request()->user()->tenant_id);
        $dataProvider->builder = $this->service->getChilds($dataProvider->builder);
    }
}
