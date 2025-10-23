<?php

namespace App\Jobs;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Multitenancy\Jobs\TenantAware;

class ImportJob implements ShouldQueue, TenantAware
{
    use Queueable;

    public $importHandler;

    /**
     * Create a new job instance.
     */
    public function __construct(public Import $import, public bool $overwrite = false)
    {
        $importClass = ImportsEnum::tryFromName($this->import->model)->value;
        $this->importHandler = new $importClass($overwrite);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resource = $this->import->file;
        Excel::import($this->importHandler, Storage::disk('public')->path($resource->getRouteValue()));

        $this->import->update([
            'status' => ImportStatusesEnum::COMPLETED->name,
            'data' => $this->importHandler->getResults(),
        ]);
    }

    public function failed()
    {
        $this->import->update([
            'status' => ImportStatusesEnum::FAILURE->name,
            'data' => $this->importHandler->getResults(),
        ]);
    }
}
