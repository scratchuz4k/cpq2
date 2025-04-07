<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Exception;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\MerchantResource;

class MerchantController extends BaseController
{
    protected $model = Merchant::class;
    /**
     * List Merchants
     * @response AnonymousResourceCollection<LengthAwarePaginator<MerchantResource>>
     */
    public function index()
    {
        $dataProvider = new DataProvider(Merchant::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return MerchantResource::collection($data);
    }

    /**
     * Store a new Merchant
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Merchant
     */
    public function show(Merchant $merchant)
    {
        //
    }

    /**
     * Update Merchant
     */
    public function update(Request $request, Merchant $merchant)
    {
        //
    }

    /**
     * Remove Merchant
     */
    public function destroy(Merchant $merchant)
    {
        $merchant->delete();
    }
}
