<?php

declare(strict_types=1);

namespace App\Jobs\Integration\CVR;

use App\Jobs\Job;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Job wrapper for CvrSendFileService
 */
class CvrSendFileJob extends Job
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param CvrFileRepositoryInterface $repository
     * @param CvrFileServiceInterface $service
     * @return bool
     * @throws Exception
     */
    public function handle(CvrFileRepositoryInterface $repository, CvrFileServiceInterface $service): bool
    {
        try {
            $service->run($repository->findByToken($this->token));
        } catch (Exception $e) {
            // catch and log

            Log::error("Error running job for sending the CVR file: ".
                "token[{$this->token}, exception[{$e->getMessage()}]"
            );

            throw $e;
        }

        return true;
    }
}
