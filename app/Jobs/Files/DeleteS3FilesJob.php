<?php

namespace App\Jobs\Files;

use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteFiles
 * @package App\Jobs\Files
 */
class DeleteS3FilesJob extends Job
{
    const SHOWROOM_FILES_KEY = 'showroom-files';

    /**
     * @var string[]
     */
    private $files;

    public $queue = 'files';

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
        try {
            foreach ($this->files as $file) {
                // Should not delete showroom files because this deletes images from FactoryVantage.com
                if (stripos($file, self::SHOWROOM_FILES_KEY) === false) {
                    Storage::disk('s3')->delete($file);
                }
            }
        } catch (\Exception $e) {
            Log::error('Files delete error.', $e->getTrace());
        }

        Log::info('Files have been successfully deleted');
    }
}
