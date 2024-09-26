<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;

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
        $tenants = auth()->user()->tenants;
        $permissions = auth()->user()->getAllPermissions();
        return Inertia::render('Account/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
        ]);
    }
}
