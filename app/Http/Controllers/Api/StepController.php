<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\StepCreateRequest;
use App\Http\Requests\StepUpdateRequest;
use App\Http\Resources\StepResource;
use App\Models\Step;
use App\Services\StepService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Support\Facades\DB;

class StepController extends BaseController
{
    protected $model = Step::class;

    public function __construct(protected StepService $stepService)
    {
        parent::__construct();
    }

    /**
     * List Steps
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<StepResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Step::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return StepResource::collection($data);
    }

    /**
     * Store a new Step
     */
    #[ExcludeRouteFromDocs]
    public function store(StepCreateRequest $request) {}

    /**
     * Show Step
     */
    #[ExcludeRouteFromDocs]
    public function show(Step $step) {}

    /**
     * Update Step
     */
    public function update(StepUpdateRequest $request, Step $step)
    {
        try {
            $step = DB::transaction(fn () => $this->stepService->update($step, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new StepResource($step);
    }

    /**
     * Remove Step
     */
    public function destroy(Step $step)
    {
        $step->delete();
    }
}
