<?php

namespace App\Http\Controllers\Web;

use App\Enums\ImportsEnum;
use App\Export\ExportCSVTemplate;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ImportCreateRequest;
use App\Http\Resources\ImportResource;
use App\Models\Import;
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
        return Inertia::render('Imports/Index', [
            'imports' => ImportsEnum::names(),
            'model' => request()->get('model') ?? null,
        ]);
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
    public function store(ImportCreateRequest $request)
    {
        try {
            DB::transaction(fn () => $this->importService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('imports.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Import $import)
    {
        return Inertia::render('Imports/Show', [
            'importObject' => new ImportResource($import),
            'resources' => $import->file,
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
        $model = $request->input('model') ?? null;
        if ($model) {
            $importClass = ImportsEnum::tryFromName($model)->value;
            $template = $importClass::getImportTemplate();

            return Excel::download(new ExportCSVTemplate($importClass, $template['data']), $template['filename']);
        }

        return parent::export($request);
    }
}
