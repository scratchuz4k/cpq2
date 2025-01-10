<?php

namespace App\Http\Controllers\Web;

use App\Models\Spa;
use App\Models\User;
use Inertia\Inertia;
use App\Models\SpaRule;
use App\Models\Supplier;
use App\Enums\PricePlanEnum;
use App\Models\SolutionType;
use App\Models\SpaOperation;
use Illuminate\Http\Request;
use App\Enums\SolutionTypesEnum;
use Illuminate\Support\Facades\DB;
use App\Services\SolutionTypeService;
use App\Http\Controllers\BaseController;
use App\Http\Resources\SolutionTypeResource;
use App\Http\Requests\SolutionTypeUpdateRequest;

class SolutionTypeController extends BaseController
{
    protected $model = SolutionType::class;

    public function __construct(protected SolutionTypeService $solutionTypeService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'SolutionTypes/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SolutionType $solutionType)
    {
        $columns = [];
        foreach (SolutionTypesEnum::cases() as $case) {
            if ($case->value == $solutionType->code) {
                $columns = $case->getColumns();
            }
        }

        return Inertia::render('SolutionTypes/Edit', [
            'solution_type' => (new SolutionTypeResource($solutionType))->resolve(),
            'price_plans' => PricePlanEnum::cases(),
            'available_columns' => $columns,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SolutionTypeUpdateRequest $request, SolutionType $solutionType)
    {
        try {
            $solutionType = DB::transaction(function () use ($solutionType, $request) {
                return $this->solutionTypeService->update($solutionType, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('solution-types.index');
        // return redirect()->route('solution-types.edit', ['solution_type' => $solutionType->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
