<?php

namespace App\Http\Controllers\Api\General;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\General\BlacklistResource;
use App\Models\General\Blacklist;
use App\Services\BlacklistService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlacklistController extends BaseController
{
    public function __construct(protected BlacklistService $blacklistService)
    {
        parent::__construct();
    }

    /**
     * List of Products
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ProductResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Blacklist::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }

        return BlacklistResource::collection($data);
    }

    public function update(Request $request, Blacklist $blacklist)
    {
        try {
            $blacklist = DB::transaction(fn () => $this->blacklistService->update($blacklist, $request->all()));
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }

        return new BlacklistResource($blacklist);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blacklist $blacklist)
    {
        $blacklist->delete();

        return response()->json(null, 204);
    }
}
