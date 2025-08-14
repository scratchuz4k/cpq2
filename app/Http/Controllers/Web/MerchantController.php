<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\MerchantCreateRequest;
use App\Http\Requests\MerchantUpdateRequest;
use App\Http\Resources\MerchantResource;
use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MerchantController extends BaseController
{
    protected $model = Merchant::class;

    public function __construct(protected MerchantService $merchantService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Merchants/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render(component: 'Merchants/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MerchantCreateRequest $request)
    {
        try {
            $merchant = DB::transaction(fn () => $this->merchantService->store($request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('merchants.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Merchant $merchant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Merchant $merchant)
    {
        $temp = new MerchantResource($merchant);

        return Inertia::render('Merchants/Edit', [
            'merchant' => $temp->toArray(request()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MerchantUpdateRequest $request, Merchant $merchant)
    {
        try {
            $merchant = DB::transaction(fn () => $this->merchantService->update($merchant, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('merchants.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Merchant $merchant)
    {
        //
    }
}
