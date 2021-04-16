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
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
            $this->send($job->payload->document);
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
                ['payload' => $job->payload->asArray(), 'validation_errors' => $job->result->validation_errors]
            );

            throw $e;
        }
    }

    /**
     * Send the file properly formatted to CVR endpoint
     *
     * @param string $filename CVR zipped filepath
     * @return void
     * @throws FileNotFoundException when the file was not found
     */
    public function send(string $filename): void
    {
        // CVR synchronization logic here
        // Also ensure to use the `Storage` facade as following: Storage::disk('tmp')->path($payload->document)
        throw new NotImplementedException;
    }
}
