<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\SolutionTypeCreateRequest;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\SolutionType;
use App\Enums\TablesEnum;
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
        return Inertia::render('SolutionTypes/Create', [
            'solutions' => SolutionTypeResource::collection(SolutionType::all())->resolve(),
            'available_tables' => TablesEnum::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SolutionTypeCreateRequest $request)
    {
        try {
            $solutionType = DB::transaction(function () use ($request) {
                return $this->solutionTypeService->store($request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return redirect()->route('solution-types.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(SolutionType $solutionType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SolutionType $solutionType)
    {
        return Inertia::render('SolutionTypes/Edit', [
            'solution_type' => (new SolutionTypeResource($solutionType))->resolve(),
            'available_tables' => TablesEnum::cases(),
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
    public function destroy(SolutionType $solutionType)
    {
        //
    }
}
