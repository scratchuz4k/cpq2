<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\Account;
use App\Models\SolutionType;

class DashboardController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userDescedents = User::descendants(auth()->id());
        $users = collect([]);
        do {
            $current = $userDescedents->pop();
            $users->push($current);
            $userDescedents->push(...$current->children);
        } while (count($userDescedents));

        return Inertia::render('Dashboard',[
            "users" => $users,
            "solutions" => SolutionType::all(),
            "status" => ["PRICE", "QUOTE", "CONFIGURE"]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function myAccount()
    {
        $tenants = auth()->user()->tenants;
        $permissions = auth()->user()->getAllPermissions();
        return Inertia::render('MyAccount/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
        ]);
    }
}
