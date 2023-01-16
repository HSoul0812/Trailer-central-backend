<?php

namespace App\Console\Commands\Files;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class ClearLocalTmpFolder
 * @package App\Console\Commands\Files
 */
class ClearLocalTmpFolder extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "files:clear-local-tmp-folder";

    private $tempMediaPath;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tempMediaPath = Storage::disk('local_tmp')->path('media');

        $this->deleteEverythingInDirectory($this->tempMediaPath);

        return 0;
    }

    private function deleteEverythingInDirectory(string $dir): void
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->deleteEverythingInDirectory($file);
            } else {
                try {
                    unlink($file);
                } catch (Exception $exception) {
                    $this->error($exception->getMessage());
                }
            }
        }

        // We don't want to delete the root dir itself
        if ($dir !== $this->tempMediaPath) {
            try {
                rmdir($dir);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }
}
