<?php

namespace App\Http\Controllers\Web;

use App\Models\Spa;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Supplier;
use App\Services\SpasService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SpaResource;
use App\Http\Requests\SpaCreateRequest;
use App\Http\Requests\SpaUpdateRequest;
use App\Http\Controllers\BaseController;
use App\Models\SpaOperation;
use App\Models\SpaRule;

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
        return Inertia::render(component: 'Spas/Index');
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
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpaCreateRequest $request)
    {
        try {
            $spa = DB::transaction(function () use ($request) {
                return $this->spasService->store($request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
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
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpaUpdateRequest $request, Spa $spa)
    {
        try {
            $spa = DB::transaction(function () use ($spa, $request) {
                return $this->spasService->update($spa, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
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
}
