<?php

namespace App\Http\Controllers\Web;

use App\Models\Spa;
use Inertia\Inertia;
use App\Models\Price;
use App\Models\Product;
use App\Services\PriceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PriceResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;

class PriceController extends BaseController
{
    protected $model = Price::class;

    public function __construct(protected PriceService $priceService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Prices/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Prices/Create', [
            'spas' => Spa::all(),
            'products' => Product::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PriceCreateRequest $request)
    {
        try {
            $price = DB::transaction(function () use ($request) {
                return $this->priceService->store($request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('prices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price)
    {
        return Inertia::render('Prices/Show', [
            'quote' => new PriceResource($price)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Price $price)
    {
        $temp = new PriceResource($price);
        return Inertia::render('Prices/Edit', [
            'price' => $temp->toArray(request()),
            'spas' => Spa::all(),
            'products' => Product::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceUpdateRequest $request, Price $price)
    {
        try {
            $price = DB::transaction(function () use ($price, $request) {
                return $this->priceService->update($price, $request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('prices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        //
    }
}
