<?php

namespace App\Jobs\Files;

use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteFiles
 * @package App\Jobs\Files
 */
class DeleteFilesJob extends Job
{
    /**
     * @var string[]
     */
    private $files;

    /**
     * DeleteFiles constructor.
     * @param string[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function handle()
    {
        Log::info('Starting deleting files');

        try {
            foreach ($this->files as $file) {
                Storage::disk('s3')->delete($file);
            }
        } catch (\Exception $e) {
            Log::error('Files delete error.', $e->getTrace());
        }

        Log::info('Files have been successfully deleted');
    }
}
