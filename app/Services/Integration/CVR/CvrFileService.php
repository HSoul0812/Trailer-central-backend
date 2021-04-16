<?php

declare(strict_types=1);

namespace App\Services\Integration\CVR;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Common\BusyJobException;
use App\Exceptions\NotImplementedException;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Models\Integration\CVR\CvrFileResult;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;
use Exception;

/**
 * Provide capabilities to setup and dispatch a monitored job for CVR file synchronizer, also provide the runner
 * to handle the zipped file parser and file GEN builder.
 *
 * This is to decouple service code from the job.
 */
class CvrFileService extends AbstractMonitoredJobService implements CvrFileServiceInterface
{
    /**
     * @var CvrFileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        CvrFileRepositoryInterface $bulkRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->fileRepository = $bulkRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $dealerId
     * @param array|CvrFilePayload $payload
     * @param string|null $token
     * @return CvrFile
     * @throws BusyJobException when there is currently other job working
     */
    public function setup(int $dealerId, $payload, ?string $token = null): CvrFile
    {
        if ($this->fileRepository->isBusyByDealer($dealerId)) {
            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->fileRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT, // this particular job has not any restriction
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);
    }

    /**
     * Run the service
     *
     * @param CvrFile $job
     * @return void
     * @throws Exception
     */
    public function run($job): void
    {
        try {
            $this->fileRepository->updateProgress($job->token, 1); // to indicate the process has begin
            $this->send($job->payload->document);
            $this->fileRepository->setCompleted($job->token);
        } catch (Exception $e) {
            $this->fileRepository->setFailed(
                $job->token, CvrFileResult::from(['validation_errors' => [$e->getMessage()]])
            );

            throw $e;
        }
    }

    /**
     * Run the service
     *
     * @param string $filename CVR zipped filepath
     * @return void
     * @throws Exception
     */
    public function send(string $filename): void
    {
        // CVR synchronization logic here
        throw new NotImplementedException;
    }
}
