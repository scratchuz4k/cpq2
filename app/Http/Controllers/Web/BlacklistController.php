<?php

namespace App\Http\Controllers\Web;

use App\Enums\CloudMarketSubprovidersEnum;
use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\ComparisonOperatorsEnum;
use App\Enums\ContractTermsEnum;
use App\Enums\SpeedUnitsEnum;
use App\Export\Export;
use App\Http\Controllers\BaseController;
use App\Http\Requests\BlacklistCreateRequest;
use App\Http\Requests\BlacklistUpdateRequest;
use App\Models\General\Blacklist;
use App\Models\Product;
use App\Services\BlacklistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BlacklistController extends BaseController
{
    protected $model = Blacklist::class;

    public function __construct(protected BlacklistService $blacklistService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Blacklists/Index', [
            'operators' => ComparisonOperatorsEnum::asArray()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Blacklists/Create', [
            'providers' => array_map(function($item) {
                if ($item["name"] === "Virtual1") {
                    $item["name"] = "PXC";
                    $item["value"] = "pxc";
                }
                return $item;
            }, CloudMarketSuppliersEnum::asArray()),
            'subproviders' => CloudMarketSubprovidersEnum::asArray(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlacklistCreateRequest $request)
    {
        try {
            $this->blacklistService->store($request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('blacklists.index');
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
    public function edit(Request $request, Blacklist $blacklist): Response
    {
        try {
            $item = $this->blacklistService->edit($request->route('blacklist'));
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return Inertia::render('Blacklists/Edit', [
            'blacklisted_item' => $item,
            'providers' => array_map(function($item) {
                if ($item["name"] === "Virtual1") {
                    $item["name"] = "PXC";
                    $item["value"] = "pxc";
                }
                return $item;
            }, CloudMarketSuppliersEnum::asArray()),
            'subproviders' => CloudMarketSubprovidersEnum::asArray(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlacklistUpdateRequest $request, Blacklist $blacklist)
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

        return redirect()->route('blacklists.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
