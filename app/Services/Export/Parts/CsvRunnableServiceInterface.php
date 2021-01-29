<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Models\Bulk\Parts\BulkDownload;
use App\Services\Common\RunnableServiceInterface;

interface CsvRunnableServiceInterface extends RunnableServiceInterface
{
    /**
     * Run the export service based on the download instance
     *
     * @param BulkDownload $job
     * @return mixed
     */
    public function run($job);
}
