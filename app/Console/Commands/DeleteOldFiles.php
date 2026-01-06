<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DeleteOldFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete files older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $count_deled_files = 0;
        $directories = [storage_path('app/products'), storage_path('app/temp')];

        foreach ($directories as $directory) {
            if (! File::exists($directory)) {
                $this->warn("Directory does not exist: $directory");

                return;
            }

            $files = File::allFiles($directory);

            foreach ($files as $file) {
                $lastModified = File::lastModified($file);

                if (now()->subDay()->timestamp > $lastModified) {
                    $file_name = $file->getRealPath();
                    File::delete($file);
                    $count_deled_files++;
                    Log::info("[JOB] DeleteOldFiles | Deleted: {$file_name}");
                }
            }
        }
        Log::info('[JOB] DeleteOldFiles | '.($count_deled_files ? "{$count_deled_files} Files older than 24 hours deleted successfully." : 'No files were deleted'));
    }
}
