<?php

namespace App\Jobs;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Queueable;

    public $importHandler;

    /**
     * Create a new job instance.
     */
    public function __construct(public Import $import, public bool $overwrite = false, public ?int $tenant_id = null)
    {
        $importClass = ImportsEnum::tryFromName($this->import->model)->value;
        $this->importHandler = new $importClass($overwrite, $tenant_id);
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
            'data' => $this->importHandler->results,
        ]);
    }

    public function failed()
    {
        $this->import->update([
            'status' => ImportStatusesEnum::FAILURE->name,
            'data' => $this->importHandler->results,
        ]);
    }
}
