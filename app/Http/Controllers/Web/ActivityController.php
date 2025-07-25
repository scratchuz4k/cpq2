<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    protected $model = Activity::class;

    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        //
    }

    protected function transformExportData(Collection $data): Collection
    {
        return new Collection($data->map(function ($item) {
            return $item;
        }));
    }
}
