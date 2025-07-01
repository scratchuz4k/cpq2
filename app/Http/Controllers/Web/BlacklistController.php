<?php

namespace App\Http\Controllers\Web;

use App\Enums\CloudMarketSubprovidersEnum;
use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\ComparisonOperatorsEnum;
use App\Enums\ContractTermsEnum;
use App\Enums\SpeedUnitsEnum;
use App\Export\Export;
use App\Http\Requests\BlacklistItemCreateRequest;
use App\Http\Requests\BlacklistItemUpdateRequest;
use App\Models\Blacklist;
use App\Services\BlacklistService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use Maatwebsite\Excel\Facades\Excel;

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
            'subproviders' => CloudMarketSubprovidersEnum::cases(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlacklistItemCreateRequest $request)
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

        return Inertia::render('Blacklists/Edit', [
            'blacklisted_item' => $item,
            'providers' => array_map(function($item) {
                if ($item["name"] === "Virtual1") {
                    $item["name"] = "PXC";
                    $item["value"] = "pxc";
                }
                return $item;
            }, CloudMarketSuppliersEnum::asArray()),
            'subproviders' => CloudMarketSubprovidersEnum::cases(),
            'operators' => ComparisonOperatorsEnum::asArray(),
            'contract_terms' => ContractTermsEnum::asArray(),
            'speed_units' => SpeedUnitsEnum::asArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlacklistItemUpdateRequest $request)
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

    public function export(Request $request)
    {
        try {
            $data =  $this->blacklistService->export($request->all());
        } catch(\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        $date = now()->format('Y-m-d');
        $model_name = class_basename($this->model);
        $filename = "Export_{$model_name}_{$date}.xlsx";

        try {
            if (!$data->count()) {
                throw new \Exception('No data Found!');
            }
            return Excel::download(new Export($this->model, $data), $filename);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(null, 204);
        }
    }

}
