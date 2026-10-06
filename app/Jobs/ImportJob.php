<?php

namespace App\Jobs;

use App\Enums\ImportsEnum;
use App\Enums\ImportStatusesEnum;
use App\Imports\UsersImport;
use App\Models\Import;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;
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
        $importClass = ImportsEnum::importClassFor(class_basename($this->import->model));
        $this->importHandler = new $importClass(auth()->user(), $this->import->model, false, $this->overwrite);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            app(PermissionRegistrar::class)->setPermissionsTeamId($this->import->tenant_id);

            $resource = $this->import->file;
            Excel::import($this->importHandler, Storage::disk($this->sourceDisk())->path($resource->getRouteValue()));

            $this->import->update([
                'status' => ImportStatusesEnum::COMPLETED->name,
                'data' => $this->importHandler->getResults(),
            ]);
        } catch (\Exception $e) {
            Log::error("[JOB] Import Job | Error processing: {$e->getMessage()}");
        } finally {
            if ($this->sourceDisk() === 'local') {
                Storage::disk('local')->delete($this->import->file->getRouteValue());
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId(null);
            Auth::forgetGuards();
        }
    }

    private function sourceDisk(): string
    {
        // User imports read the unmasked private copy; jobs queued before it existed only have the public file.
        return $this->importHandler instanceof UsersImport
            && $this->import->file
            && Storage::disk('local')->exists($this->import->file->getRouteValue())
            ? 'local'
            : 'public';
    }

    public function failed()
    {
        $this->import->update([
            'status' => ImportStatusesEnum::FAILURE->name,
            'data' => $this->importHandler->getResults(),
        ]);
    }
}
