<?php

declare(strict_types=1);

namespace App\Jobs\Bulk\Parts;

use App\Jobs\Job;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class FinancialReportExportJob extends Job
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
     * @param BulkReportRepositoryInterface $repository
     * @param BulkReportJobServiceInterface $service
     * @return bool
     * @throws Throwable
     */
    public function handle(BulkReportRepositoryInterface $repository, BulkReportJobServiceInterface $service): bool
    {
        $model = $repository->findByToken($this->token);

        try {
            $service->run($model);
        } catch (Throwable $exception) {

            $payload = implode(',', $model->payload->asArray());

            Log::error("Error running export parts financial report export job: " .
                "token[{$model->token}, payload={{$payload}}] exception[{$exception->getMessage()}]"
            );

            throw $exception;
        }

        return true;
    }
}
