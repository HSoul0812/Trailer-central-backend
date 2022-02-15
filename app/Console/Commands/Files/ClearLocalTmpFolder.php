<?php

namespace App\Console\Commands\Files;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $localTmpFolderPath = Storage::disk('local_tmp')->getAdapter()->getPathPrefix();

        return File::cleanDirectory($localTmpFolderPath);
    }
}
