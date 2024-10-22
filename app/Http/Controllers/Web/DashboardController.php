<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\SolutionType;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();

        $users = User::descendants();
        if (!$user->hasRole('Sales Rep')) {
            $users = User::all();
        }

        $users = $users->filter(fn ($user) => $user->orders->count() > 0);

        return Inertia::render('Dashboard', [
            "users" => $users->values(),
            "solutions" => SolutionType::all(),
            "status" => ["PRICE", "QUOTE", "CONFIGURE"]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function myAccount()
    {
        $user = request()->user();
        $tenants =  $user->hasRole('Super Admin') ? Tenant::all() : $user->tenants;
        $permissions = $user->getAllPermissions();
        return Inertia::render('MyAccount/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
        ]);
    }
}
