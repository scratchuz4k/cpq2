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
    protected $description = 'Delete prices products older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::withContext(['signature' => $this->signature]);
        Log::info('Job Started.');

        $count_deled_files = 0;
        $directory = storage_path('app/products');

        if (!File::exists($directory)) {
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
                Log::info("Deleted: {$file_name}");
            }
        }

        Log::info($count_deled_files
            ? "{$count_deled_files} Files older than 24 hours deleted successfully."
            : 'No files were deleted'
        );

        Log::info('Job Finished.');
        Log::withContext([]);
    }
}
