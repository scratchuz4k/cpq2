<?php

namespace App\Http\Controllers\Web;

use App\Models\Account;
use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class AccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = auth()->user()->tenants;
        $permissions = auth()->user()->getAllPermissions();
        return Inertia::render('Account/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard');
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
    public function update(Request $request, Account $account)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
