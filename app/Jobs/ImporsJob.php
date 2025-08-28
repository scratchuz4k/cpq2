<?php

namespace App\Jobs;

use App\Enums\ImportStatusesEnum;
use App\Imports\ProductsImport;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;

class ImporsJob implements ShouldQueue
{
    use Queueable;

    public ProductsImport $productsImport;

    /**
     * Create a new job instance.
     */
    public function __construct(public Import $import, public bool $override = false)
    {
        $this->productsImport = new ProductsImport($override);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resource = $this->import->file;

        Excel::import($this->productsImport, $resource->route);

        $this->import->update([
            'status' => ImportStatusesEnum::COMPLETED->name,
            'data' => $this->productsImport->results,
        ]);
    }

    public function failed()
    {
        $this->import->update([
            'status' => ImportStatusesEnum::FAILURE->name,
            'data' => $this->productsImport->results,
        ]);
    }
}
