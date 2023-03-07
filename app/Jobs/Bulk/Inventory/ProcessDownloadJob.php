<?php

declare(strict_types=1);

namespace App\Jobs\Bulk\Inventory;

use App\Models\Bulk\Inventory\BulkDownload;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use App\Jobs\Job;
use Throwable;

class ProcessDownloadJob extends Job
{
    /** @var string */
    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param BulkDownloadRepositoryInterface $repository
     * @param BulkDownloadJobServiceInterface $service
     * @param LoggerInterface $logger
     * @return bool
     * @throws Throwable
     * @throws ModelNotFoundException
     */
    public function handle(BulkDownloadRepositoryInterface $repository,
                           BulkDownloadJobServiceInterface $service,
                           LoggerInterface                 $logger
    ): bool
    {
        $job = $repository->findByToken($this->token);

        if ($job === null) {
            throw new ModelNotFoundException(sprintf('No query results for model [%s] %s', BulkDownload::class, $this->token));
        }

        try {
            $service->handler($job->payload->output)->export($job);
        } catch (Throwable $exception) {

            $payload = json_encode($job->payload->asArray());

            $logger->error("Error running download inventory job: " .
                "token[{$job->token}, payload={$payload}, exception={$exception->getMessage()}]"
            );

            throw $exception;
        }

        return true;
    }
}
