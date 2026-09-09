<?php

namespace App\Jobs;

use App\Enums\ImportStatusesEnum;
use App\Imports\Import as ImportHandler;
use App\Models\Import;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Multitenancy\Jobs\TenantAware;
use Spatie\Permission\PermissionRegistrar;

class ImportJob implements ShouldQueue, TenantAware
{
    use Queueable;

    public $importHandler;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Import $import,
        public ?User $actingUser = null,
        public bool $overwrite = false
    ) {
        $this->importHandler = new ImportHandler($actingUser, $this->import->model);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            app(PermissionRegistrar::class)->setPermissionsTeamId($this->import->tenant_id);

            $resource = $this->import->file;
            Excel::import($this->importHandler, Storage::disk('public')->path($resource->getRouteValue()));

            $this->import->update([
                'status' => ImportStatusesEnum::COMPLETED->name,
                'data' => $this->importHandler->getResults(),
            ]);
        } catch (\Exception $e) {
            Log::error("[JOB] Import Job | Error processing: {$e->getMessage()}");
        } finally {
            app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        }
    }

    public function failed()
    {
        $this->import->update([
            'status' => ImportStatusesEnum::FAILURE->name,
            'data' => $this->importHandler->getResults(),
        ]);
    }
}
