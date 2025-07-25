<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RegionCreateRequest;
use App\Http\Requests\RegionUpdateRequest;
use App\Http\Resources\RegionResource;
use App\Models\Merchant;
use App\Models\Region;
use App\Models\User;
use App\Services\RegionsService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegionController extends BaseController
{
    protected $model = Region::class;

    public function __construct(protected RegionsService $regionsService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Regions/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Regions/Create', [
            'users' => User::all(),
            'merchants' => Merchant::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegionCreateRequest $request)
    {
        try {
            $region = DB::transaction(fn () => $this->regionsService->store($request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('regions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Region $region)
    {
        $temp = new RegionResource($region);

        return Inertia::render('Regions/Edit', [
            'region' => $temp->toArray(request()),
            'users' => User::all(),
            'merchants' => Merchant::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RegionUpdateRequest $request, Region $region)
    {
        try {
            $region = DB::transaction(fn () => $this->regionsService->update($region, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('regions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        //
    }
}
