<?php

declare(strict_types=1);

namespace App\Services\Integration\CVR;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;
use Exception;

/**
 * Provide capabilities to setup and dispatch a monitored job for CVR file sender, also provide the runner
 * to handle the sending process.
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
        CvrFileRepositoryInterface $fileRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->fileRepository = $fileRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $dealerId
     * @param array|CvrFilePayload $payload
     * @param string|null $token
     * @return CvrFile
     */
    public function setup(int $dealerId, $payload, ?string $token = null): CvrFile
    {
        return $this->fileRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => CvrFile::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT, // this particular job has not any restriction
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);
    }

    /**
     * Run the service
     *
     * @param CvrFile $job
     * @return void
     * @throws Exception when any potential exception has been caught and logged
     */
    public function run($job): void
    {
        try {
            $this->logger->info(sprintf('%s: the job %s has been started', __CLASS__, $job->token));

            $this->fileRepository->updateProgress($job->token, 1); // to indicate the process has begin
            $this->sendFile($job);
            $this->fileRepository->setCompleted($job->token);

            $this->logger->info(sprintf('%s: the job %s was finished', __CLASS__, $job->token));
        } catch (Exception $e) {
            $this->fileRepository->setFailed($job->token, ['message' => 'Got exception: ' . $e->getMessage()]);
            $this->logger->error(
                sprintf(
                    '%s: the job %s has failed, exception: %s',
                    __CLASS__,
                    $job->token,
                    $e->getMessage()
                ),
                ['payload' => $job->payload->asArray(), 'errors' => $job->result->errors]
            );

            throw $e;
        }
    }

    public function sendFile(CvrFile $job): void
    {
        // CVR build/synchronization logic here

        // I suggest to use the `Storage` facade as following: Storage::disk('tmp')->path($this->buildFile())
        // In case you want to update the progress of the job,
        // you could do it as following $this->fileRepository->updateProgress($job-token, 50);
        // When it has popped up some error, just throw an exception with the properly exception message, it will be
        // attached to the job to be able to track those errors
        throw new NotImplementedException;
    }

    /**
     * @return string file path where is stored the assembled file ready to be sent
     */
    public function buildFile(CvrFile $job): string
    {
        // CVR build file logic here
        // Also I suggest to use the `Storage`  facade as following: Storage::disk('tmp')->put($filename)
        throw new NotImplementedException;
    }
}
