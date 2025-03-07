<?php

namespace App\Http\Controllers\Web;

use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\ComparisonOperatorsEnum;
use App\Enums\ContractTermsEnum;
use App\Enums\SpeedUnitsEnum;
use App\Models\Tenant;
use App\Services\BlacklistService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Brand;
use Inertia\Response;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\PricePlan;

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
        return Inertia::render('Blacklist/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Blacklist/Create', [
            'providers' => CloudMarketSuppliersEnum::asArray(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->blacklistService->store($request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('blacklist.index');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request): Response
    {
        try {
            $item = $this->blacklistService->edit($request->route('blacklist'));
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return Inertia::render('Blacklist/Edit', [
            'blacklisted_item' => $item,
            'providers' => CloudMarketSuppliersEnum::asArray(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $this->blacklistService->update(
                $request->all()
            );
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return redirect()->route('blacklist.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
