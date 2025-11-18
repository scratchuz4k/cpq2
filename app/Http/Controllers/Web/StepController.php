<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StepCreateRequest;
use App\Http\Requests\StepUpdateRequest;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Http\Resources\StepResource;
use App\Models\Step;
use App\Services\StepService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StepController extends BaseController
{
    protected $model = Step::class;

    public function __construct(protected StepService $stepService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Step/Index', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Step/Create', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StepCreateRequest $request)
    {
        try {
            $step = DB::transaction(fn () => $this->stepService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('steps.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Step $step)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Step $step)
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Step/Edit', [
            'step' => (new StepResource($step))->resolve(),
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StepUpdateRequest $request, Step $step)
    {
        try {
            $step = DB::transaction(fn () => $this->stepService->update($step, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('steps.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Step $step) {}
}
