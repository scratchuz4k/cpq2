<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\General\Blacklist;
use App\Services\BlacklistService;
use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

#[ExcludeAllRoutesFromDocs]
class BlacklistController extends BaseController
{

    protected $model = Blacklist::class;

    public function __construct(protected BlacklistService $blacklistService)
    {
        parent::__construct();
    }

    /**
     * List of blacklist items
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
     * Create a new blacklist item
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show blacklist item
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update blacklist item
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove blacklist item
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
