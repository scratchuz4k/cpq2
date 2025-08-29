<?php

namespace App\Http\Controllers\Web;

use App\Export\ImportTemplate;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ImportCreateRequest;
use App\Http\Resources\ImportResource;
use App\Jobs\ImporsJob;
use App\Models\Import;
use App\Models\Product;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends BaseController
{
    protected $model = Import::class;

    public function __construct(protected ImportService $importService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Imports/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Imports/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ImportCreateRequest $request)
    {
        try {
            $import = DB::transaction(fn () => $this->importService->store($request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        ImporsJob::dispatch($import, $import->overwrite);

        return redirect()->route('imports.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Import $import)
    {
        return Inertia::render('Imports/Show', [
            'importObject' => new ImportResource($import),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Import $import)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Import $import)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Import $import)
    {
        //
    }

    /**
     * Download CSV import template file.
     */
    public function export(Request $request)
    {
        $p = Product::all();

        $dummyProduct = Product::factory()->count(10)->make();

        $dummyProduct->each(function ($item) {
            $item->price_code = fake()->optional()->numberBetween(1, 999);
            $item->price_cost = $item->price_code ? fake()->optional()->numberBetween(1, 999) : null;
            $item->price_net = $item->price_code ? fake()->optional()->numberBetween(1, 999) : null;
            $item->parent_sku = fake()->optional()->slug(2);
        });

        //        $dummyPrice = Price::factory()->count(10)->make();
        $filename = 'import_products_template.csv';

        return Excel::download(new ImportTemplate(Product::class, $dummyProduct), $filename);
    }
}
