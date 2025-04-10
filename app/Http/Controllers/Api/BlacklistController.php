<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Tenant;
use App\Services\BlacklistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlacklistController extends BaseController
{

    public function __construct(protected BlacklistService $blacklistService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $blacklist_items = $this->blacklistService->index();
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return $blacklist_items;
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->blacklistService->destroy($id);
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
    }
}
