<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SpaCreateRequest;
use App\Http\Requests\SpaUpdateRequest;
use App\Http\Resources\SpaResource;
use App\Models\PricePlan;
use App\Models\Spa;
use App\Models\SpaOperation;
use App\Models\SpaRule;
use App\Models\Supplier;
use App\Services\SpasService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SpaController extends BaseController
{
    protected $model = Spa::class;

    public function __construct(protected SpasService $spasService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Spas/Index', [
            'rules' => SpaRule::all(),
            'price_plans' => PricePlan::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Spas/Create', [
            'rules' => SpaRule::all(),
            'operations' => SpaOperation::all(),
            'suppliers' => Supplier::all(),
            'price_plans' => PricePlan::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpaCreateRequest $request)
    {
        try {
            $spa = DB::transaction(fn () => $this->spasService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('spas.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Spa $spa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Spa $spa)
    {
        $temp = new SpaResource($spa);

        return Inertia::render('Spas/Edit', [
            'spa' => $temp->toArray(request()),
            'rules' => SpaRule::all(),
            'operations' => SpaOperation::all(),
            'suppliers' => Supplier::all(),
            'price_plans' => PricePlan::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpaUpdateRequest $request, Spa $spa)
    {
        try {
            $spa = DB::transaction(fn () => $this->spasService->update($spa, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('spas.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spa $spa)
    {
        //
    }

    protected function transformExportData(Collection $data): Collection
    {
        return new Collection($data->map(fn ($item) => [
            'id' => $item->id,
            'rule' => $item->rule?->name ?? null,
            'level_1' => app($item->rule->entity_1)::where('id', $item->field_1)->first()->name ?? null,
            'level_2' => $item->rule->entity_2 ? app($item->rule->entity_2)::where('id', $item->field_2)->first()->name : null,
            'price_plan' => $item->pricePlan?->name ?? null,
            'operation' => $item->operation?->code ?? null,
            'value' => $item->value,
            'percentage' => $item->percentage,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]));
    }
}
