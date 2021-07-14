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
use App\Services\Dms\CVR\DTOs\CVRFileDTO;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\CvrCreds;
use App\Services\Dms\CVR\CVRGeneratorServiceInterface;
use GuzzleHttp\Client;
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
     * @var CVRGeneratorServiceInterface 
     */
    private $cvrGeneratorService;
    
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
        MonitoredJobRepositoryInterface $monitoredJobsRepository,
        CVRGeneratorServiceInterface $cvrGeneratorService
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->fileRepository = $fileRepository;
        $this->cvrGeneratorService = $cvrGeneratorService;
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
        $cvrFilePath = $this->buildFile($job)->getFilePath();    
        $cvrCreds = $this->getCvrFileCreds($job);
        
        $client = new Client();
        $response = $client->request('POST', config('cvr.api_endpoint'), [
            'auth' => [
                $cvrCreds->cvr_username, 
                $cvrCreds->cvr_password
            ],
            'body' => file_get_contents($cvrFilePath),
            'headers' => [
                'FileName' => config('cvr.unique_id'). "_" . uniqid(),
                'Content-Type' => 'application/xml'
            ]
        ]);                
    } 

    /**
     * {@inheritDoc}
     */
    public function buildFile(CvrFile $job): CVRFileDTO
    {
        $payload = $job->payload->asArray();
        $unitSale = UnitSale::findOrFail($payload['unit_sale_id']);        
        return $this->cvrGeneratorService->generate($unitSale);
    }
    
    private function getCvrFileCreds(CvrFile $job) : CvrCreds
    {
        $payload = $job->payload->asArray();
        $unitSale = UnitSale::findOrFail($payload['unit_sale_id']);   
        return CvrCreds::where('dealer_id', $unitSale->dealer->dealer_id)->firstOrFail();
    }
}
