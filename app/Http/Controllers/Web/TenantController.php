<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Models\Resource;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TenantController extends BaseController
{
    public function __construct(protected TenantService $tenantService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $files = [];

        $isSuperAdmin = auth()->user()->hasRole('Super Admin');
        $tenants = $isSuperAdmin
            ? Tenant::all()
            : auth()->user()->tenants()->get();

        foreach ($tenants as $tenant) {
            if ($tenant->config_id) {
                $resource = Resource::find($tenant->config_id);

                $file = $resource->getRouteValue();
                if (Storage::exists($file)) {
                    $files[$tenant->id] = Storage::get($file) ?? null;
                }
            }
        }

        return Inertia::render('Tenant/index', [
            'isSuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'files' => $files,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        try {
            $tenant = $this->tenantService->update($tenant, $request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('tenant.index', ['tenant' => $tenant->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
