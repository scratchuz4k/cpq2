<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Permission;
use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Illuminate\Http\Request;

#[ExcludeAllRoutesFromDocs]
class PermissionController extends BaseController
{
    /**
     * List of Permissions
     */
    public function index()
    {
        //
    }

    /**
     * Store a new Permission
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Permission
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update Permission
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove Permission
     */
    public function destroy(string $id)
    {
        //
    }
}
