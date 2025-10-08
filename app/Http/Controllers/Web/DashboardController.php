<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Policies\ExportPolicy;
use Inertia\Inertia;

class DashboardController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Dashboard');
    }

    /**
     * Display a listing of the resource.
     */
    public function myAccount()
    {
        $user = request()->user();
        $tenants = $user->tenants;
        $permissions = $user->getAllPermissions();

        return Inertia::render('MyAccount/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function reporting()
    {
        //        $this->authorize('viewAny export', ExportPolicy::class);

        return Inertia::render('MyAccount/Reporting');
    }
}
