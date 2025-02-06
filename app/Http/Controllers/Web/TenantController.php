<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\TenantUpdateRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TenantController extends BaseController
{
    protected $model = Tenant::class;

    public function __construct(protected TenantService $tenantService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();

        return Inertia::render('Tenant/Index', [
            'isSuperAdmin' => $user->hasRole('Super Admin'),
            'tenants' => $user->currentTenant->load('file', 'logo')
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
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return redirect()->route('tenants.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantUpdateRequest $request, Tenant $tenant)
    {
        try {
            $tenant = DB::transaction(function () use ($tenant, $request) {
                return $this->tenantService->update($tenant, $request->all());
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return Inertia::location('tenants');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function switch(Request $request, Tenant $tenant)
    {
        $request->user()->update(['tenant_id' => $tenant->id]);
        $tenant->makeCurrent();

        return Inertia::location('/');
    }
}
