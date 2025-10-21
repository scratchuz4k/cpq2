<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\MerchantResource;
use App\Models\Merchant;
use App\Services\MerchantService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MerchantController extends BaseController
{
    protected $model = Merchant::class;

    public function __construct(protected MerchantService $service)
    {
        parent::__construct();
    }

    /**
     * List Merchants
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<MerchantResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Merchant::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return MerchantResource::collection($data);
    }

    /**
     * Store a new Merchant
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Merchant
     */
    #[ExcludeRouteFromDocs]
    public function show(Merchant $merchant)
    {
        //
    }

    /**
     * Update Merchant
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Merchant $merchant)
    {
        try {
            $merchant = DB::transaction(fn () => $this->service->update($merchant, $request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new MerchantResource($merchant);
    }

    /**
     * Remove Merchant
     */
    public function destroy(Merchant $merchant)
    {
        $merchant->delete();
    }
}
