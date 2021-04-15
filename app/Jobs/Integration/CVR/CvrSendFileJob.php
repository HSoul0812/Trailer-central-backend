<?php

declare(strict_types=1);

namespace App\Jobs\Integration\CVR;

use App\Jobs\Job;
use App\Models\Integration\CVR\CvrFile;
use App\Services\Common\RunnableJobServiceInterface;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Job wrapper for CvrSendFileService
 */
class CvrSendFileJob extends Job
{
    /**
     * @var CvrFile
     */
    private $jobFile;

    /**
     * @var RunnableJobServiceInterface
     */
    private $service;

    public function __construct(CvrFile $jobFile)
    {
        $this->service = app(BulkDownloadMonitoredJobServiceInterface::class);
        $this->jobFile = $jobFile;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function handle(): bool
    {
        try {
            $this->service->run($this->jobFile);
        } catch (Exception $e) {
            // catch and log

            $payload = implode(',',$this->jobFile->payload->asArray());

            Log::error("Error running CVR file synchronizer job: ".
                "token[{$this->jobFile->token}, payload={{$payload}}] exception[{$e->getMessage()}]"
            );

            throw $e;
        }

        return true;
    }
}
